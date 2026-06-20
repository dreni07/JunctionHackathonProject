<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aggregated structural statistics per floor level (Table 1.3).
     */
    public function up(): void
    {
        Schema::create('building_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->unique();             // -1 .. 4
            $table->string('label');                        // Basement Level, Ground Level, ...
            $table->unsignedInteger('active_boxes');
            $table->unsignedInteger('box_footprint_sqm');
            $table->unsignedInteger('tumo_nodes');
            $table->unsignedInteger('public_nodes');
            $table->unsignedInteger('max_human_load');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_levels');
    }
};
