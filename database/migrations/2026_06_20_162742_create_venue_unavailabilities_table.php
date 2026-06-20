<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Periods (or indefinite spells) when a venue is unavailable — blocked off or
 * out of service (broken). Null start/end means "from now / forever".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('venue_unavailabilities')) {
            return;
        }

        Schema::create('venue_unavailabilities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('space_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // blocked | broken
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['space_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_unavailabilities');
    }
};
