<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The single source of truth for the booking calendar. Every space-time
     * hold lives here so the conflict-checking agent answers "is this space
     * free?" with ONE query against this table instead of scanning events.
     *
     * A reservation can be tied to a pending proposal (a tentative hold) and/or
     * a confirmed event.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('space_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('final_proposal_id')->nullable()->constrained()->nullOnDelete();

            // Full datetimes make overlap detection a trivial range query.
            $table->timestamp('start_at');
            $table->timestamp('end_at');

            $table->string('status')->default('tentative'); // BookingStatus

            $table->timestamps();

            // The index the conflict query rides on.
            $table->index(['space_id', 'start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
