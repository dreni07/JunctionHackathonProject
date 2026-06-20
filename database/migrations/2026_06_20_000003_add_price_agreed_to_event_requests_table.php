<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The final price the organizer actually agreed to (may differ from the
     * agent's suggestion after a negotiation). This is what the pricing dataset
     * learns from.
     */
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->decimal('price_agreed', 10, 2)->nullable()->after('price_suggested');
        });
    }

    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->dropColumn('price_agreed');
        });
    }
};
