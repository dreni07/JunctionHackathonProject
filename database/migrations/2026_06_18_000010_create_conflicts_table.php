<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detected scheduling, space, asset, or setup conflicts.
     */
    public function up(): void
    {
        Schema::create('conflicts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('event_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('final_proposal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('space_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type'); // ConflictType
            $table->string('status')->default('open')->index(); // ConflictStatus
            $table->string('severity')->default('medium'); // RiskLevel

            $table->string('title');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conflicts');
    }
};
