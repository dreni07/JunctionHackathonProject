<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every accept / reject decision a worker makes on an event request, with the
 * reason — the training set the agent learns from (reinforcement learning on
 * its own booking decisions).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_decisions')) {
            return;
        }

        Schema::create('event_decisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_request_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('matched_space_id')->nullable()->constrained('spaces')->nullOnDelete();

            $table->string('decision'); // accepted | rejected
            $table->string('rejection_reason')->nullable(); // a coded reason bucket
            $table->text('notes')->nullable();

            // Snapshot of the request at decision time, for learning.
            $table->string('event_type')->nullable();
            $table->unsignedInteger('attendees')->nullable();
            $table->decimal('price_suggested', 12, 2)->nullable();
            $table->decimal('price_agreed', 12, 2)->nullable();
            $table->json('features')->nullable();

            $table->timestamps();

            $table->index(['decision', 'created_at']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_decisions');
    }
};
