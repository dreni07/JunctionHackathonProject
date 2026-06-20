<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The price the agent suggested for this request (total + per m²), stored
     * when the organizer accepts it.
     */
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->decimal('price_suggested', 10, 2)->nullable()->after('matched_space_id');
            $table->decimal('price_per_sqm', 8, 2)->nullable()->after('price_suggested');
        });
    }

    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->dropColumn(['price_suggested', 'price_per_sqm']);
        });
    }
};
