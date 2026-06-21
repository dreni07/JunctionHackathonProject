<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrects invoices.organization_id, which was originally created as a bigint
 * (foreignId) even though organizations use UUID primary keys — causing a
 * "Data truncated for column 'organization_id'" error when seeding. Re-creates
 * it as a UUID foreign key. Safe to run on a database where it is already a
 * UUID (it becomes a no-op).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoices') || ! Schema::hasColumn('invoices', 'organization_id')) {
            return;
        }

        // Already the right type? Nothing to do.
        $type = Schema::getColumnType('invoices', 'organization_id');
        if (! in_array($type, ['integer', 'bigint', 'biginteger'], true)) {
            return;
        }

        // Drop the foreign key if one exists (ignore if it doesn't).
        try {
            Schema::table('invoices', function (Blueprint $table): void {
                $table->dropForeign(['organization_id']);
            });
        } catch (Throwable) {
            // No foreign key to drop.
        }

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn('organization_id');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreignUuid('organization_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        // No safe automatic reversal — the original bigint type was a bug.
    }
};
