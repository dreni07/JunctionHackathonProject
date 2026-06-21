<?php

use App\Models\Space;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('spaces', 'tenant_id')) {
            Schema::table('spaces', function (Blueprint $table): void {
                $table->foreignId('tenant_id')->nullable()->after('zone_class')
                    ->constrained()->nullOnDelete();
            });
        }

        Space::query()->whereNull('tenant_id')->each(function (Space $space): void {
            $tenantId = Tenant::resolveSpaceTenantId(
                (string) $space->zone_class,
                (string) $space->functional_type,
            );

            if ($tenantId !== null) {
                $space->update(['tenant_id' => $tenantId]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('spaces', 'tenant_id')) {
            Schema::table('spaces', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }
    }
};
