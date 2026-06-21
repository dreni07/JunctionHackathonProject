<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');                      // Blue Hall, Orange Hall, ...
            $table->integer('floor')->default(0);        // 0 or -1 for the main halls
            $table->unsignedInteger('capacity')->default(0);
            $table->string('type');                      // SpaceType: hall | workshop_room | ...
            $table->json('features')->nullable();        // {livestream: true, projector: true, ...}
            $table->json('location_geometry')->nullable(); // optional floor-plan coordinates
            $table->timestamps();
        });

        // NOTE: space bookings live in the dedicated `reservations` table
        // (see create_reservations_table) — the single calendar/source of truth
        // the conflict-checking agent reads.
    }

    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
