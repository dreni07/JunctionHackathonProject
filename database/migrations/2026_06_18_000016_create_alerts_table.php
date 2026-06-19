<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Operational alerts raised by agents, conflict checks, inventory, etc.
     */
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('event_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('final_proposal_id')->nullable()->constrained()->nullOnDelete();

            $table->string('source')->default('agent'); // AlertSource
            $table->string('severity')->default('medium'); // RiskLevel
            $table->string('status')->default('unread')->index(); // AlertStatus

            $table->string('title');
            $table->text('message');
            $table->string('agent_name')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['event_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
