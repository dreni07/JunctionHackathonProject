<?php

use App\Enums\TenantWorkerRole;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Tenant::query()->each(function (Tenant $tenant): void {
            $roles = $tenant->roles ?? [];

            if (in_array(TenantWorkerRole::Manager->value, $roles, true)) {
                return;
            }

            array_unshift($roles, TenantWorkerRole::Manager->value);

            $tenant->update(['roles' => array_values($roles)]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Tenant::query()->each(function (Tenant $tenant): void {
            $roles = array_values(array_filter(
                $tenant->roles ?? [],
                fn (string $role): bool => $role !== TenantWorkerRole::Manager->value,
            ));

            $tenant->update(['roles' => $roles]);
        });
    }
};
