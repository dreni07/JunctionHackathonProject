<?php

declare(strict_types=1);

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('organization accounts redirect to planner after login', function (): void {
    $user = User::factory()->organization()->create([
        'email' => 'organizer@example.com',
    ]);
    $user->syncRoles(RoleName::Organizer);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertRedirect(route('planner', absolute: false));

    expect(auth()->user()?->account_type)->toBe(AccountType::Organization);
});

test('unverified organization accounts can open the planner after login', function (): void {
    $user = User::factory()->organization()->unverified()->create([
        'email' => 'pending@example.com',
    ]);
    $user->syncRoles(RoleName::Organizer);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertRedirect(route('planner', absolute: false));

    $this->actingAs($user)
        ->get(route('planner'))
        ->assertOk();
});

test('operational workers redirect to operations after login', function (): void {
    $worker = User::factory()->operational()->create([
        'email' => 'worker@pyramid.test',
    ]);
    $worker->syncRoles(RoleName::Operations);

    $this->post(route('login.store'), [
        'email' => $worker->email,
        'password' => 'password',
    ])
        ->assertRedirect(route('operations.home', absolute: false));
});
