<?php

declare(strict_types=1);

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('organization accounts are redirected away from the operations dashboard page', function (): void {
    $user = User::factory()->organization()->create();
    $user->syncRoles(RoleName::Organizer);

    $this->actingAs($user)
        ->get(route('operations.home'))
        ->assertRedirect(route('dashboard'));
});

test('operational workers are redirected from the organization dashboard to operations', function (): void {
    $user = User::factory()->operational()->create();
    $user->syncRoles(RoleName::Operations);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('operations.home'));
});

test('organization accounts can open the organization dashboard', function (): void {
    $user = User::factory()->organization()->create();
    $user->syncRoles(RoleName::Organizer);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('operational workers can open the operations dashboard page', function (): void {
    $user = User::factory()->operational()->create();
    $user->syncRoles(RoleName::Operations);

    $this->actingAs($user)
        ->get(route('operations.home'))
        ->assertOk();
});

test('organization accounts cannot access operations json api', function (): void {
    $user = User::factory()->organization()->create();
    $user->syncRoles(RoleName::Organizer);

    $this->actingAs($user)
        ->getJson(route('operations.dashboard'))
        ->assertForbidden();
});

test('operational workers can log in through the public login form', function (): void {
    $worker = User::factory()->operational()->create([
        'email' => 'worker@pyramid.test',
    ]);
    $worker->syncRoles(RoleName::Operations);

    $this->post(route('login.store'), [
        'email' => $worker->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($worker);
    expect(auth()->user()?->account_type)->toBe(AccountType::Operational);
});

test('organization accounts can log in through the public login form', function (): void {
    $user = User::factory()->organization()->create([
        'email' => 'organizer@example.com',
    ]);
    $user->syncRoles(RoleName::Organizer);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    expect(auth()->user()?->account_type)->toBe(AccountType::Organization);
});
