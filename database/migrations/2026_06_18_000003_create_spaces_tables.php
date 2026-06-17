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

        // Availability / booking slots. A row with a non-null event_id means
        // the space is booked for that window — the conflict engine reads this.
        Schema::create('space_availability', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('space_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['space_id', 'date']); // fast availability/conflict lookups
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('space_availability');
        Schema::dropIfExists('spaces');
    }
};
