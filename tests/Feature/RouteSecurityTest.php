<?php

declare(strict_types=1);

use App\Models\User;

test('guests are redirected to login when opening the planner', function (): void {
    $this->get(route('planner'))
        ->assertRedirect(route('login'));
});

test('authenticated organization accounts can open the planner', function (): void {
    $user = User::factory()->organization()->create();

    $this->actingAs($user)
        ->get(route('planner'))
        ->assertOk();
});

test('operational workers are redirected away from the planner', function (): void {
    $worker = User::factory()->operational()->create();

    $this->actingAs($worker)
        ->get(route('planner'))
        ->assertRedirect(route('operations.home'));
});

test('operational workers are redirected away from organization event routes', function (string $routeName): void {
    $worker = User::factory()->operational()->create();

    $this->actingAs($worker)
        ->get(route($routeName))
        ->assertRedirect(route('operations.home'));
})->with([
    'my-events.index',
    'dashboard',
    'notifications.index',
]);

test('unverified organization accounts are redirected to email verification', function (string $routeName): void {
    $user = User::factory()->organization()->unverified()->create();

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertRedirect(route('verification.notice'));
})->with([
    'planner',
    'my-events.index',
    'dashboard',
]);

test('unverified operational workers are redirected to email verification', function (): void {
    $worker = User::factory()->operational()->unverified()->create();

    $this->actingAs($worker)
        ->get(route('operations.home'))
        ->assertRedirect(route('verification.notice'));
});

test('guests cannot access protected application routes', function (string $routeName): void {
    $this->get(route($routeName))
        ->assertRedirect(route('login'));
})->with([
    'chat.index',
    'documents.index',
    'facility',
    'ocr.index',
    'pyramid.ingest.index',
    'pyramid.knowledge.index',
]);

test('guests cannot access the operations dashboard page', function (): void {
    $this->get(route('operations.home'))
        ->assertRedirect(route('login'));
});
