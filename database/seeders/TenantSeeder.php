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

class TenantSeeder extends Seeder
{
    /**
     * Seed the three Pyramid branches, a demo worker for each, and backfill
     * the account type on any pre-existing accounts. Idempotent.
     */
    public function run(): void
    {
        $tenants = [
            [
                'title' => 'TUMO TIRANA',
                'description' => 'Free-of-charge after-school program where teens design and build with technology.',
                'roles' => ['Manager', 'Learning Coach', 'Workshop Leader', 'Studio Manager', 'Front Desk'],
            ],
            [
                'title' => 'ICT ECOSYSTEM',
                'description' => "The technology hub powering the Pyramid's digital products and infrastructure.",
                'roles' => ['Manager', 'Software Engineer', 'DevOps Engineer', 'Product Manager', 'Data Analyst'],
            ],
            [
                'title' => 'ARTS',
                'description' => 'Exhibitions, performances, and cultural programming across the Pyramid.',
                'roles' => ['Manager', 'Curator', 'Gallery Manager', 'Event Producer', 'Technician'],
            ],
        ];

        foreach ($tenants as $data) {
            $tenant = Tenant::updateOrCreate(
                ['title' => $data['title']],
                ['description' => $data['description'], 'roles' => $data['roles']],
            );

            // One demo operational worker per branch (password "password").
            $worker = User::updateOrCreate(
                ['email' => Str::slug($data['title']).'@pyramid.test'],
                [
                    'name' => Str::headline(Str::lower($data['title'])).' Worker',
                    'password' => Hash::make('password'),
                    'account_type' => AccountType::Operational->value,
                    'tenant_id' => $tenant->id,
                    'worker_role' => collect($data['roles'])
                        ->first(fn (string $role): bool => $role !== TenantWorkerRole::Manager->value),
                    'email_verified_at' => now(),
                ],
            );

            $worker->assignRole(RoleName::Operations);
        }

        // Any account created before account types existed signs in through
        // the organization door.
        User::whereNull('account_type')->update([
            'account_type' => AccountType::Organization->value,
        ]);
    }
}
