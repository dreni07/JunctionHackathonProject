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
            $user = User::create([
                'name' => $account['name'],
                'email' => $account['email'],
                'account_type' => AccountType::Organization->value,
                'password' => Hash::make('password'),
            ]);

            $user->assignRole($account['role']);
        }

        // Tenants (Pyramid branches) and their operational demo workers.
        $this->call(TenantSeeder::class);

        // 30 operational workers spread across tenants and roles.
        $this->call(WorkforceSeeder::class);
    }
}
