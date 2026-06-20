<?php

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
    $this->seed(RolePermissionSeeder::class);
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users register as organization accounts with the organizer role', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    expect($user->account_type)->toBe(AccountType::Organization);
    expect($user->tenant_id)->toBeNull();
    expect($user->worker_role)->toBeNull();
    expect($user->organization_id)->not->toBeNull();
    expect($user->hasRole(RoleName::Organizer))->toBeTrue();
    expect($user->hasRole(RoleName::Operations))->toBeFalse();
    expect($user->isOrganization())->toBeTrue();
    expect($user->isOperational())->toBeFalse();
});

test('registration rejects operational worker fields', function () {
    $this->post(route('register.store'), [
        'email' => 'org@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'account_type' => AccountType::Operational->value,
        'tenant_id' => '1',
        'worker_role' => 'Curator',
    ])->assertSessionHasErrors(['account_type', 'tenant_id', 'worker_role']);
});
