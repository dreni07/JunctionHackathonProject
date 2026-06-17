<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignUuid('event_request_id')
                ->nullable()
                ->after('organization_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('final_proposals', function (Blueprint $table) {
            $table->foreignUuid('event_request_id')
                ->nullable()
                ->after('organization_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('final_proposals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_request_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_request_id');
        });
    }
};
