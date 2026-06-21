<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type')->nullable(); // OrganizationType: university | company | ngo | ...
            $table->timestamps();
        });

        // An organizer (and the events they create) belongs to an organization.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('organization_id')->nullable()->after('email')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::dropIfExists('organizations');
    }
};
