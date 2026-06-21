<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('operational workers cannot open the profile completion page', function (): void {
    $worker = User::factory()->operational()->create();
    $worker->syncRoles(RoleName::Operations);

    $this->actingAs($worker)
        ->get(route('profile.complete'))
        ->assertRedirect(route('operations.home'));
});

test('organization accounts can open the profile completion page', function (): void {
    $organizer = User::factory()->organization()->create();
    $organizer->syncRoles(RoleName::Organizer);

    $this->actingAs($organizer)
        ->get(route('profile.complete'))
        ->assertOk();
});

test('operational workers do not receive profile completion in shared auth props', function (): void {
    $worker = User::factory()->operational()->create();
    $worker->syncRoles(RoleName::Operations);

    $this->actingAs($worker)
        ->get(route('operations.home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('auth.profileCompletion', null));
});

test('organization accounts receive profile completion in shared auth props', function (): void {
    $organizer = User::factory()->organization()->create();
    $organizer->syncRoles(RoleName::Organizer);

    $this->actingAs($organizer)
        ->get(route('planner'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('auth.profileCompletion', 0));
});
