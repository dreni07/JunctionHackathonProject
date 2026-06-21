<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        // One demo account per role (all share the password "password"). These
        // sign in through the organization door.
        $accounts = [
            ['name' => 'Olta Organizer', 'email' => 'organizer@pyramid.test', 'role' => RoleName::Organizer],
            ['name' => 'Ops Team', 'email' => 'operations@pyramid.test', 'role' => RoleName::Operations],
            ['name' => 'Manager Mira', 'email' => 'management@pyramid.test', 'role' => RoleName::Management],
        ];

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'account_type' => AccountType::Organization->value,
                    'password' => Hash::make('password'),
                ],
            );

            $user->assignRole($account['role']);
        }

        // Tenants (Pyramid branches) and their operational demo workers.
        $this->call(TenantSeeder::class);

        // One tenant manager per branch (creates operational managers).
        $this->call(TenantManagerSeeder::class);

        // Branch finance: budgets, invoices, payments, and expenses.
        $this->call(TenantFinanceSeeder::class);

        // 50 operational workers per tenant (150 total across three branches).
        $this->call(WorkforceSeeder::class);

        // Full facility appendix: rooms, levels, standards, rules, infrastructure.
        $this->call(PyramidFacilitySeeder::class);

        // Historical event pricing — the pricing agent's training data.
        $this->call(PricingReferenceSeeder::class);

        // Existing calendar bookings so the scheduling agent has real conflicts.
        $this->call(ReservationSeeder::class);

        // Events, tasks, and alerts so the operations dashboard has data.
        $this->call(OperationsDemoSeeder::class);

        // A demo organizer with a portfolio of booked events for "My events".
        $this->call(OrganizationPortfolioSeeder::class);

        // Default floor-plan positions for any venue not yet pinned.
        $this->call(SpaceMapDefaultsSeeder::class);
    }
}
