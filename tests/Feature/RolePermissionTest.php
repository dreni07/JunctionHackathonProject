<?php

use App\Authorization\Permissions;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(RolePermissionSeeder::class);
});

it('grants an organizer only their own request permissions', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleName::Organizer);

    expect($user->hasRole(RoleName::Organizer))->toBeTrue();
    expect($user->hasPermissionTo(Permissions::REQUESTS_CREATE))->toBeTrue();
    expect($user->hasPermissionTo(Permissions::EVENTS_MANAGE))->toBeFalse();
    expect($user->hasPermissionTo(Permissions::USERS_MANAGE))->toBeFalse();
});

it('lets operations manage events but not approve quotations or manage users', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleName::Operations);

    expect($user->hasPermissionTo(Permissions::EVENTS_MANAGE))->toBeTrue();
    expect($user->hasPermissionTo(Permissions::INVENTORY_MANAGE))->toBeTrue();
    expect($user->hasPermissionTo(Permissions::QUOTATIONS_APPROVE))->toBeFalse();
    expect($user->hasPermissionTo(Permissions::USERS_MANAGE))->toBeFalse();
});

it('gives management every operations permission plus approvals and user admin', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleName::Management);

    expect($user->hasPermissionTo(Permissions::EVENTS_MANAGE))->toBeTrue();
    expect($user->hasPermissionTo(Permissions::QUOTATIONS_APPROVE))->toBeTrue();
    expect($user->hasPermissionTo(Permissions::USERS_MANAGE))->toBeTrue();
    expect($user->hasPermissionTo(Permissions::SPACES_MANAGE))->toBeTrue();
});

it('resolves permissions through the Gate so routes can use can: middleware', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleName::Management);

    actingAs($user);

    // Gate::before wires permission names to abilities.
    expect($user->can(Permissions::QUOTATIONS_APPROVE))->toBeTrue();

    $organizer = User::factory()->create();
    $organizer->assignRole(RoleName::Organizer);

    expect($organizer->can(Permissions::QUOTATIONS_APPROVE))->toBeFalse();
});
