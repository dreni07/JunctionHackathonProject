<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Venues an alert relates to (the "related entities" of a raised alert).
     */
    public function up(): void
    {
        if (Schema::hasTable('alert_space')) {
            return;
        }

        Schema::create('alert_space', function (Blueprint $table): void {
            $table->foreignUuid('alert_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('space_id')->constrained()->cascadeOnDelete();
            $table->primary(['alert_id', 'space_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_space');
    }
};
