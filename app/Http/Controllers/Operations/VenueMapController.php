<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Enums\VenueUnavailabilityType;
use App\Models\Space;
use App\Models\VenueUnavailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Floor Explorer: every floor plan, the venues pinned on each, and tools
 * to block a venue or mark it out of service (which then shows red).
 */
class VenueMapController extends OperationsController
{
    private const MAX_LEVELS = 3;

    public function index(): Response
    {
        $spaces = Space::query()
            ->with(['unavailabilities' => fn ($q) => $q->orderByRaw('starts_at is null desc')->orderBy('starts_at')])
            ->orderBy('floor')
            ->orderBy('box_ref')
            ->orderBy('name')
            ->get();

        return Inertia::render('operations/venue-map', [
            'floors' => $this->floors(),
            'venues' => $spaces->map(fn (Space $space): array => $this->serialize($space))->values(),
            'maxLevels' => self::MAX_LEVELS,
        ]);
    }

    /**
     * Block a venue, or mark it out of service, for a period (or indefinitely).
     */
    public function storeUnavailability(Request $request, Space $space): JsonResponse
    {
        return $this->handleOperation(function () use ($request, $space): JsonResponse {
            $validated = $request->validate([
                'type' => ['required', Rule::enum(VenueUnavailabilityType::class)],
                'starts_at' => ['nullable', 'date'],
                'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
                'reason' => ['nullable', 'string', 'max:500'],
            ]);

            $space->unavailabilities()->create([
                'type' => $validated['type'],
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'reason' => $validated['reason'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            return $this->json($this->serialize($space->fresh('unavailabilities')), 201);
        });
    }

    /**
     * Lift a block / out-of-service marking — making the venue available again.
     */
    public function destroyUnavailability(Space $space, VenueUnavailability $unavailability): JsonResponse
    {
        return $this->handleOperation(function () use ($space, $unavailability): JsonResponse {
            abort_unless($unavailability->space_id === $space->id, 404);

            $unavailability->delete();

            return $this->json($this->serialize($space->fresh('unavailabilities')));
        });
    }

    /**
     * @return array<int, string|null>
     */
    private function floors(): array
    {
        $urls = [];

        for ($level = 1; $level <= self::MAX_LEVELS; $level++) {
            $file = "pyramid-plan-{$level}.png";
            $path = public_path('assets/'.$file);

            if (! is_file($path) && $level === 1 && is_file(public_path('assets/pyramid-plan.png'))) {
                $file = 'pyramid-plan.png';
                $path = public_path('assets/pyramid-plan.png');
            }

            $urls[$level] = is_file($path) ? '/assets/'.$file.'?v='.filemtime($path) : null;
        }

        return $urls;
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(Space $space): array
    {
        $current = $space->currentUnavailability();

        return [
            'id' => $space->id,
            'name' => $space->name,
            'box_ref' => $space->box_ref,
            'room_code' => $space->room_code,
            'zone_class' => $space->zone_class,
            'functional_type' => $space->functional_type,
            'floor' => $space->floor,
            'capacity' => $space->capacity,
            'area_sqm' => $space->area_sqm,
            'location_geometry' => $space->location_geometry,
            'level' => $space->location_geometry['level'] ?? 1,
            'is_unavailable' => $current !== null,
            'current_status' => $current !== null ? [
                'type' => $current->type->value,
                'type_label' => $current->type->label(),
                'describe' => $current->describe(),
            ] : null,
            'unavailabilities' => $space->unavailabilities
                ->map(fn (VenueUnavailability $u): array => [
                    'id' => $u->id,
                    'type' => $u->type->value,
                    'type_label' => $u->type->label(),
                    'starts_at' => $u->starts_at?->toIso8601String(),
                    'ends_at' => $u->ends_at?->toIso8601String(),
                    'reason' => $u->reason,
                    'describe' => $u->describe(),
                ])
                ->all(),
        ];
    }
}
