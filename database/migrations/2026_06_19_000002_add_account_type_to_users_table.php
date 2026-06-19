<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An operational worker signs into a specific tenant (branch) with a
     * specific worker role; an organization account leaves both null.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // AccountType: "organization" | "operational".
            $table->string('account_type')->nullable()->after('email');
            // The branch an operational worker belongs to.
            $table->foreignId('tenant_id')->nullable()->after('account_type')
                ->constrained()->nullOnDelete();
            // The worker's role within their tenant (one of tenants.roles).
            $table->string('worker_role')->nullable()->after('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn(['account_type', 'worker_role']);
        });
    }
};
