<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('invoices')) {
            return;
        }

        Schema::create('invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('final_proposal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('title');
            $table->decimal('amount', 14, 2);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
