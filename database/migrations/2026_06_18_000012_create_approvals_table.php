<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Formal approval decisions on proposals and other approvable records.
     */
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuidMorphs('approvable');

            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('decision')->default('pending'); // ApprovalDecision
            $table->text('notes')->nullable();
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id', 'decision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
