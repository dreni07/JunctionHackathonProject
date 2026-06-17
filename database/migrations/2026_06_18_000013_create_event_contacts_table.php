<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Organizer / stakeholder contacts tied to a request or event.
     */
    public function up(): void
    {
        Schema::create('event_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('event_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('organization_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('role')->default('primary'); // primary | technical | finance
            $table->boolean('is_primary')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_contacts');
    }
};
