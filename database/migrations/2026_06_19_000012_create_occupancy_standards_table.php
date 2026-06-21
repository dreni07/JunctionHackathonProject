<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Human occupancy density & structural safety standards (Table 1.1) — the
     * m²/person metric used to compute a room's safe maximum capacity.
     */
    public function up(): void
    {
        Schema::create('occupancy_standards', function (Blueprint $table) {
            $table->id();
            $table->string('functional_category')->unique(); // Active Technology Labs, ...
            $table->decimal('area_metric_sqm', 4, 2);         // 2.0, 3.5, ...
            $table->text('allocation_rule');                  // Operational Space Allocation Rule
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occupancy_standards');
    }
};
