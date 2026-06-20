<?php

namespace App\Http\Controllers;

use App\Models\AcousticRule;
use App\Models\BlackoutWindow;
use App\Models\BuildingLevel;
use App\Models\FacilityProfile;
use App\Models\InfrastructureSpec;
use App\Models\OccupancyStandard;
use App\Models\Space;
use App\Models\ZoneOperatingRule;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController extends Controller
{
    /**
     * The full Pyramid facility dataset seeded from the appendix.
     */
    public function index(): Response
    {
        return Inertia::render('facility', [
            'profile' => FacilityProfile::query()->first(),
            'occupancyStandards' => OccupancyStandard::query()->orderBy('id')->get(),
            'levels' => BuildingLevel::query()->orderBy('level')->get(),
            'rooms' => Space::query()
                ->whereNotNull('room_code')
                ->orderBy('floor')
                ->orderBy('box_ref')
                ->get(['room_code', 'box_ref', 'floor', 'zone_class', 'functional_type', 'area_sqm', 'capacity', 'workload_target']),
            'zoneRules' => ZoneOperatingRule::query()->orderBy('id')->get(),
            'blackoutWindows' => BlackoutWindow::query()->orderBy('id')->get(),
            'acousticRules' => AcousticRule::query()->orderBy('id')->get(),
            'infrastructureSpecs' => InfrastructureSpec::query()->orderBy('id')->get(),
        ]);
    }
}
