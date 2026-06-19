<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Feed consumed by the operational changes poll endpoint.
     */
    public function up(): void
    {
        Schema::create('operational_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('model_type');
            $table->string('model_id');
            $table->string('action'); // created | updated | deleted

            $table->string('summary');
            $table->json('payload')->nullable();

            $table->timestamp('occurred_at');

            $table->timestamps();

            $table->index(['occurred_at', 'id']);
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_changes');
    }
};
