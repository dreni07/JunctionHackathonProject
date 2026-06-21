<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Operational tasks the AI agent splits an event into and assigns to
     * Pyramid workers. Each task belongs to an event and (optionally) to the
     * client organization. The assignee (user_id) must be an operational
     * worker — enforced in the application layer, see Task::assignTo().
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('organization_id')->nullable()->constrained()->nullOnDelete();

            // The assigned Pyramid worker (nullable until assigned).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Basics for now — we can grow this later.
            $table->string('name');
            $table->text('description')->nullable();

            $table->string('state')->default('pending')->index(); // TaskState

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
