<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperationalLoginSeeder extends Seeder
{
    /**
     * One verified operational worker for quick local login.
     */
    public function run(): void
    {
        $tenant = Tenant::query()->first();

        if ($tenant === null) {
            $this->call(TenantSeeder::class);
            $tenant = Tenant::query()->firstOrFail();
        }

        $workerRole = collect($tenant->roles ?? [])
            ->first(fn (string $role): bool => $role !== 'Manager') ?? 'Technician';

        $user = User::updateOrCreate(
            ['email' => 'worker@pyramid.test'],
            [
                'name' => 'Demo Worker',
                'password' => Hash::make('password'),
                'account_type' => AccountType::Operational->value,
                'tenant_id' => $tenant->id,
                'worker_role' => $workerRole,
                'email_verified_at' => now(),
            ],
        );

        if (! $user->hasRole(RoleName::Operations)) {
            $user->assignRole(RoleName::Operations);
        }

        $this->command?->info('Login: worker@pyramid.test / password');
    }
}
