<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration redirects new users to the planner', function (): void {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('planner', absolute: false));

    $this->get(route('planner'))
        ->assertOk();
});

test('registration still works when roles have not been seeded yet', function (): void {
    $response = $this->post(route('register.store'), [
        'email' => 'fresh-db@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('planner', absolute: false));

    $user = User::query()->where('email', 'fresh-db@example.com')->firstOrFail();

    expect($user->hasRole(RoleName::Organizer))->toBeTrue();
    expect($user->hasVerifiedEmail())->toBeTrue();
});
