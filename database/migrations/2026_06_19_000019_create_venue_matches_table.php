<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The venue-matching agent's output: a confidence score for every candidate
     * venue against an event request, plus whether it was free on the calendar
     * and which one was finally selected.
     */
    public function up(): void
    {
        Schema::create('venue_matches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_request_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('space_id')->constrained('spaces')->cascadeOnDelete();
            $table->decimal('confidence_score', 5, 2);
            $table->unsignedInteger('rank');
            $table->boolean('available')->default(false);
            $table->boolean('selected')->default(false);
            $table->timestamps();

            $table->index(['event_request_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_matches');
    }
};
