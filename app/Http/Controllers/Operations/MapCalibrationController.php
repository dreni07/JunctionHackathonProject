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
    /** The Pyramid is calibrated across up to this many floor plans. */
    private const MAX_LEVELS = 3;

    /**
     * The calibration workspace: the per-floor plan images plus every venue and
     * its currently-pinned position (if any).
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
            'planUrls' => $this->planUrls(),
            'maxLevels' => self::MAX_LEVELS,
        ]);
    }

    /**
     * Upload (or replace) the floor-plan image for one level of the Pyramid.
     */
    public function uploadPlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan' => ['required', 'image', 'max:12288'],
            'level' => ['required', 'integer', 'min:1', 'max:'.self::MAX_LEVELS],
        ]);

        $directory = public_path('assets');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $level = (int) $validated['level'];
        $request->file('plan')->move($directory, $this->planFile($level));

        return response()->json(['data' => [
            'level' => $level,
            'plan_url' => $this->planUrl($level, true),
        ]]);
    }

    /**
     * The URLs of every uploaded floor plan, keyed by level.
     *
     * @return array<int, string|null>
     */
    private function planUrls(): array
    {
        $urls = [];

        for ($level = 1; $level <= self::MAX_LEVELS; $level++) {
            $urls[$level] = $this->planUrl($level);
        }

        return $urls;
    }

    private function planFile(int $level): string
    {
        return "pyramid-plan-{$level}.png";
    }

    /**
     * The URL of a level's plan image (cache-busted), or null if none yet.
     * Level 1 falls back to the legacy single-plan file.
     */
    private function planUrl(int $level, bool $bustCache = false): ?string
    {
        $file = $this->planFile($level);
        $path = public_path('assets/'.$file);

        if (! is_file($path) && $level === 1 && is_file(public_path('assets/pyramid-plan.png'))) {
            $file = 'pyramid-plan.png';
            $path = public_path('assets/pyramid-plan.png');
        }

        if (! is_file($path)) {
            return null;
        }

        $version = $bustCache ? time() : filemtime($path);

        return '/assets/'.$file.'?v='.$version;
    }

    /**
     * Save (or clear) a venue's pinned position on a given floor plan.
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
            'level' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_LEVELS],
        ]);

        $geometry = isset($validated['x'], $validated['y'])
            ? [
                'x' => round((float) $validated['x'], 4),
                'y' => round((float) $validated['y'], 4),
                'level' => (int) ($validated['level'] ?? 1),
            ]
            : null;

        $space->update(['location_geometry' => $geometry]);

        return response()->json(['data' => [
            'id' => $space->id,
            'location_geometry' => $geometry,
        ]]);
    }
}
