<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Inbound inquiries from external organizers — the front door before a
     * proposal or confirmed event exists.
     */
    public function up(): void
    {
        Schema::create('event_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();

            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('event_type')->nullable(); // EventType
            $table->unsignedInteger('attendees')->nullable();
            $table->timestamp('preferred_start_at')->nullable();
            $table->timestamp('preferred_end_at')->nullable();

            $table->text('raw_intake')->nullable(); // original free-text / AI transcript

            $table->string('status')->default('submitted')->index(); // EventRequestStatus

            $table->foreignUuid('final_proposal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_requests');
    }
};
