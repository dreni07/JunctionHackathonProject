<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WorkforceSeeder extends Seeder
{
    private const TARGET_USERS = 30;

    /**
     * Seed 30 operational workers spread across every tenant and cycling through
     * each tenant's worker roles. Idempotent — emails are deterministic, so a
     * re-run updates the same accounts instead of duplicating them.
     */
    public function run(): void
    {
        $tenants = Tenant::query()->orderBy('id')->get();

        if ($tenants->isEmpty()) {
            $this->call(TenantSeeder::class);
            $tenants = Tenant::query()->orderBy('id')->get();
        }

        $tenantCount = $tenants->count();
        $base = intdiv(self::TARGET_USERS, $tenantCount);
        $remainder = self::TARGET_USERS % $tenantCount;
        $seeded = 0;

        foreach ($tenants as $tenantIndex => $tenant) {
            /** @var list<string> $roles */
            $roles = array_values($tenant->roles ?? []);

            if ($roles === []) {
                continue;
            }

            // Spread the target evenly; earlier tenants absorb any remainder.
            $quota = $base + ($tenantIndex < $remainder ? 1 : 0);

            /** @var array<string, int> $roleSequence */
            $roleSequence = [];

            for ($i = 0; $i < $quota; $i++) {
                $role = $roles[$i % count($roles)];
                $roleSequence[$role] = ($roleSequence[$role] ?? 0) + 1;

                $email = sprintf(
                    '%s-%s-%d@pyramid.test',
                    Str::slug($tenant->title),
                    Str::slug($role),
                    $roleSequence[$role],
                );

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => fake()->name(),
                        'password' => Hash::make('password'),
                        'account_type' => AccountType::Operational->value,
                        'tenant_id' => $tenant->id,
                        'worker_role' => $role,
                        'email_verified_at' => now(),
                    ],
                );

                $user->assignRole(RoleName::Operations);
                $seeded++;
            }
        }

        $this->command?->info("Seeded {$seeded} operational workers across {$tenantCount} tenants.");
    }
}
