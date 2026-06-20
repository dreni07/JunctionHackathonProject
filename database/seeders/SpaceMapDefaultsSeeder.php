<?php

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Seeder;

/**
 * Gives every venue a default position on the floor-1 plan so the venue map
 * shows up everywhere out of the box. Real, precise placement is done in the
 * Map calibration tool — this only fills in venues that have not been pinned
 * yet (it never overwrites a calibrated position).
 */
class SpaceMapDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $spaces = Space::query()
            ->whereNull('location_geometry')
            ->orderBy('floor')
            ->orderBy('box_ref')
            ->orderBy('name')
            ->get();

        $columns = 8;
        $filled = 0;

        foreach ($spaces->values() as $index => $space) {
            $column = $index % $columns;
            $row = intdiv($index, $columns);

            $space->update(['location_geometry' => [
                'x' => round(0.10 + $column * 0.11, 3),
                'y' => round(0.20 + $row * 0.12, 3),
                'level' => 1,
            ]]);
            $filled++;
        }

        $this->command?->info("Placed {$filled} venues on the floor-1 plan (defaults — refine in Map calibration).");
    }
}
