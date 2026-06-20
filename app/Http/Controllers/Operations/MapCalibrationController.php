<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Space;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Lets an operational worker pin each venue onto the Pyramid floor plan once.
 * The captured point (normalized 0–1 against the plan image) is stored in
 * spaces.location_geometry and later used to highlight the venue the agent
 * recommends.
 */
class MapCalibrationController extends Controller
{
    /**
     * The calibration workspace: the plan image plus every venue and its
     * currently-pinned position (if any).
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $spacesQuery = Space::query()
            ->orderBy('floor')
            ->orderBy('box_ref')
            ->orderBy('name');

        if ($user !== null && $user->isOperational() && $user->tenant_id !== null) {
            $spacesQuery->where('tenant_id', $user->tenant_id);
        }

        $spaces = $spacesQuery
            ->get(['id', 'name', 'box_ref', 'room_code', 'zone_class', 'floor', 'capacity', 'location_geometry'])
            ->map(fn (Space $space): array => [
                'id' => $space->id,
                'name' => $space->name,
                'box_ref' => $space->box_ref,
                'room_code' => $space->room_code,
                'zone_class' => $space->zone_class,
                'floor' => $space->floor,
                'capacity' => $space->capacity,
                'location_geometry' => $space->location_geometry,
            ]);

        return Inertia::render('operations/map-calibration', [
            'spaces' => $spaces,
            'planUrl' => $this->planUrl(),
        ]);
    }

    /**
     * Upload (or replace) the Pyramid floor-plan image used for calibration.
     * It is stored at the fixed public path both the calibration tool and the
     * organizer-facing map already reference.
     */
    public function uploadPlan(Request $request): JsonResponse
    {
        $request->validate([
            'plan' => ['required', 'image', 'max:12288'],
        ]);

        $directory = public_path('assets');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $request->file('plan')->move($directory, 'pyramid-plan.png');

        return response()->json(['data' => ['plan_url' => $this->planUrl(true)]]);
    }

    /**
     * The URL of the uploaded plan image (cache-busted), or null if none yet.
     */
    private function planUrl(bool $bustCache = false): ?string
    {
        $path = public_path('assets/pyramid-plan.png');

        if (! is_file($path)) {
            return null;
        }

        $version = $bustCache ? time() : filemtime($path);

        return '/assets/pyramid-plan.png?v='.$version;
    }

    /**
     * Save (or clear) a venue's pinned position on the plan.
     */
    public function update(Request $request, Space $space): JsonResponse
    {
        $user = $request->user();

        if ($user !== null && $user->isOperational() && $user->tenant_id !== null && $space->tenant_id !== $user->tenant_id) {
            abort(403, 'You are not allowed to calibrate this space.');
        }

        $validated = $request->validate([
            'x' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'y' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ]);

        $geometry = isset($validated['x'], $validated['y'])
            ? ['x' => round((float) $validated['x'], 4), 'y' => round((float) $validated['y'], 4)]
            : null;

        $space->update(['location_geometry' => $geometry]);

        return response()->json(['data' => [
            'id' => $space->id,
            'location_geometry' => $geometry,
        ]]);
    }
}
