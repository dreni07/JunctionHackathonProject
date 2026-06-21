<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The single source of truth. Fields are nullable because an event is
        // filled in progressively (draft -> collecting_info -> planning ...).
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('draft')->index();      // EventStatus
            $table->string('event_type')->nullable();                 // EventType
            $table->unsignedInteger('attendees')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->decimal('budget', 12, 2)->nullable();

            $table->foreignUuid('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });

        // Structured AI memory — what the agent understands the event needs.
        // One row per event.
        Schema::create('event_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('needs_livestream')->default(false);
            $table->boolean('needs_catering')->default(false);
            $table->boolean('needs_workshops')->default(false);
            $table->string('setup_type')->nullable(); // SetupType: theater | classroom | mixed | exhibition
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Agent brain snapshot — how far along the planning is.
        // One row per event.
        Schema::create('event_state', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('stage')->default('collecting'); // AgentStage
            $table->unsignedTinyInteger('completion_score')->default(0); // 0-100
            $table->json('missing_fields')->nullable();
            $table->string('risk_level')->nullable(); // RiskLevel: low | medium | high
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_state');
        Schema::dropIfExists('event_requirements');
        Schema::dropIfExists('events');
    }
};
