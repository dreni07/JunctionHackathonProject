<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The proposal the system makes TO the Pyramid. It carries the proposed
     * space, capacity, price and a snapshot of the event data. Only when the
     * Pyramid accepts it does a real Event get created (event_id is filled in
     * at that point).
     */
    public function up(): void
    {
        Schema::create('final_proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Who/what the proposal is for.
            $table->foreignUuid('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // The core proposal.
            $table->foreignUuid('proposed_space_id')->nullable()->constrained('spaces')->nullOnDelete();
            $table->unsignedInteger('proposed_capacity')->nullable(); // number of people
            $table->decimal('proposed_price', 12, 2)->nullable();      // the proposal money

            // Snapshot of the event data the proposal is built from.
            $table->string('title')->nullable();
            $table->string('event_type')->nullable(); // EventType
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->text('description')->nullable();
            $table->json('event_data')->nullable();   // any other event details

            $table->string('status')->default('draft')->index(); // ProposalStatus

            // Set once accepted → the Event instance created from this proposal.
            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_proposals');
    }
};
