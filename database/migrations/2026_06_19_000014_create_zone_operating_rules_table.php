<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Temporal bounds and AI enforcement protocol per zone (Table 2.1).
     */
    public function up(): void
    {
        Schema::create('zone_operating_rules', function (Blueprint $table) {
            $table->id();
            $table->string('zone_classification')->unique(); // TUMO Educational Zone, ...
            $table->string('weekday_hours');                 // "09:00 - 19:00" / "24 / 7 Access"
            $table->string('weekend_hours');
            $table->text('enforcement_protocol');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_operating_rules');
    }
};
