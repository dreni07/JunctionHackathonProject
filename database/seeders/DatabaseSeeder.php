<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        // One demo account per role (all share the password "password").
        $accounts = [
            ['name' => 'Olta Organizer', 'email' => 'organizer@pyramid.test', 'role' => RoleName::Organizer],
            ['name' => 'Ops Team', 'email' => 'operations@pyramid.test', 'role' => RoleName::Operations],
            ['name' => 'Manager Mira', 'email' => 'management@pyramid.test', 'role' => RoleName::Management],
        ];

        foreach ($accounts as $account) {
            $user = User::factory()->create([
                'name' => $account['name'],
                'email' => $account['email'],
            ]);

            $user->assignRole($account['role']);
        }
    }
}
