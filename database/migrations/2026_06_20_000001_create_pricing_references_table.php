<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historical event pricing — the training data the pricing agent learns
     * from. Seeded from the mock dataset, then continuously appended to as the
     * agent's own accepted suggestions become new reference rows (a feedback /
     * "reinforcement" loop).
     */
    public function up(): void
    {
        Schema::create('pricing_references', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('dataset')->index();  // dataset | agent
            $table->string('organizer')->nullable();
            $table->string('event_type')->index();
            $table->string('venue_name')->nullable();
            $table->string('floor')->nullable();
            $table->unsignedInteger('area_sqm');
            $table->unsignedInteger('duration_days')->default(1);
            $table->unsignedInteger('attendees')->default(0);
            $table->decimal('price_eur', 10, 2);
            $table->decimal('price_per_sqm', 8, 2);
            $table->text('notes')->nullable();
            $table->foreignUuid('event_request_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_references');
    }
};
