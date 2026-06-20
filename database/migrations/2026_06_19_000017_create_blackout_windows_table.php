<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Security/maintenance blackout intervals when no events may be scheduled
     * (Section 2.4): zone cleanup blocks, weekend freezes, nightly facility locks.
     */
    public function up(): void
    {
        Schema::create('blackout_windows', function (Blueprint $table) {
            $table->id();
            $table->string('scope');        // TUMO Educational Zone | Global Facility | ...
            $table->string('days');         // Weekdays | Weekends | Daily
            $table->string('start_time');   // "19:00"
            $table->string('end_time');     // "20:00"
            $table->text('reason');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blackout_windows');
    }
};
