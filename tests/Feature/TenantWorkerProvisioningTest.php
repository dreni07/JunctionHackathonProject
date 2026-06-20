<?php

declare(strict_types=1);

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Enums\TenantWorkerRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
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

test('tenant managers can list and create workers for their branch', function (): void {
    $manager = User::factory()->tenantManager()->create();
    $manager->syncRoles(RoleName::Operations);

    $this->actingAs($manager)
        ->getJson(route('operations.team.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'workers',
                'assignable_roles',
            ],
        ])
        ->assertJsonPath('data.assignable_roles', $manager->tenant?->assignableWorkerRoles());

    $this->actingAs($manager)
        ->postJson(route('operations.team.store'), [
            'name' => 'New Technician',
            'email' => 'new-tech@pyramid.test',
            'password' => 'password',
            'worker_role' => 'Technician',
        ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'new-tech@pyramid.test');

    $created = User::query()->where('email', 'new-tech@pyramid.test')->first();

    expect($created)->not->toBeNull();
    expect($created->tenant_id)->toBe($manager->tenant_id);
    expect($created->account_type)->toBe(AccountType::Operational);
    expect($created->worker_role)->toBe('Technician');
    expect($created->hasRole(RoleName::Operations))->toBeTrue();
});

test('non managers cannot access tenant team endpoints', function (): void {
    $worker = User::factory()->operational()->create();
    $worker->syncRoles(RoleName::Operations);

    $this->actingAs($worker)
        ->getJson(route('operations.team.index'))
        ->assertForbidden();

    $this->actingAs($worker)
        ->postJson(route('operations.team.store'), [
            'name' => 'Blocked User',
            'email' => 'blocked@pyramid.test',
            'password' => 'password',
            'worker_role' => 'Technician',
        ])
        ->assertForbidden();
});

test('tenant managers cannot assign invalid worker roles through provisioning', function (): void {
    $manager = User::factory()->tenantManager()->create();
    $manager->syncRoles(RoleName::Operations);

    $this->actingAs($manager)
        ->from(route('operations.home'))
        ->post(route('operations.team.store'), [
            'name' => 'Another Manager',
            'email' => 'another-manager@pyramid.test',
            'password' => 'password',
            'worker_role' => TenantWorkerRole::Manager->value,
        ])
        ->assertRedirect(route('operations.home'))
        ->assertSessionHasErrors(['worker_role']);
});
