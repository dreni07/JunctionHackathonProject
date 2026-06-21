<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The building-wide reference parameters from the appendix header and the
     * aggregated facility totals (Table 1.3, TOTAL CORE row). One row.
     */
    public function up(): void
    {
        Schema::create('facility_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('total_footprint_sqm');   // 11,835
            $table->decimal('height_m', 5, 2);                // 24.5
            $table->unsignedInteger('levels');                // 6
            $table->text('access_points');                    // Western Lift Matrix & 130 Exterior Terraced Steps
            $table->text('allocation_rule');                  // 50% Non-Profit / 50% Commercial
            $table->unsignedInteger('active_box_area_sqm');   // 5,012
            $table->unsignedInteger('total_boxes');           // 48
            $table->unsignedInteger('tumo_nodes');            // 24
            $table->unsignedInteger('public_nodes');          // 24
            $table->unsignedInteger('max_human_load');        // 2,549
            $table->string('reference_baseline')->nullable(); // Module Core v1.3
            $table->text('source')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_profiles');
    }
};
