<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('manage boring things page lists registered organization contacts', function (): void {
    $worker = User::factory()->operational()->create();
    $worker->syncRoles(RoleName::Operations);

    $organizer = User::factory()->organization()->create([
        'name' => 'Northstar Events',
        'email' => 'northstar@pyramid.test',
    ]);
    $organizer->syncRoles(RoleName::Organizer);

    $this->actingAs($worker)
        ->get(route('operations.manage-boring-things'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('organizationContacts', 1)
            ->where('organizationContacts.0.email', 'northstar@pyramid.test')
            ->where('organizationContacts.0.name', 'Northstar Events'));
});
