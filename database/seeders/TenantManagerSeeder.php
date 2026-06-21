<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Enums\TenantWorkerRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantManagerSeeder extends Seeder
{
    /**
     * Seed one tenant manager per Pyramid branch. Managers sign in at /login
     * and can provision operational workers for their branch.
     */
    public function run(): void
    {
        Tenant::query()->orderBy('id')->each(function (Tenant $tenant): void {
            $user = User::updateOrCreate(
                ['email' => sprintf('%s-manager@pyramid.test', Str::slug($tenant->title))],
                [
                    'name' => $tenant->title.' Manager',
                    'password' => Hash::make('password'),
                    'account_type' => AccountType::Operational->value,
                    'tenant_id' => $tenant->id,
                    'worker_role' => TenantWorkerRole::Manager->value,
                    'email_verified_at' => now(),
                ],
            );

            $user->assignRole(RoleName::Operations);
        });
    }
}
