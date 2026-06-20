<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The venue the matching + scheduling agents settled on for this request.
     */
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->foreignUuid('matched_space_id')->nullable()->after('attendees')
                ->constrained('spaces')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('matched_space_id');
        });
    }
};
