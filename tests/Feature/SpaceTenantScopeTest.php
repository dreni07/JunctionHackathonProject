<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\Space;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PyramidFacilitySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TenantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(TenantSeeder::class);
    $this->seed(PyramidFacilitySeeder::class);
});

test('tumo workers only see tumo tenant spaces', function (): void {
    $tumo = Tenant::query()->where('title', 'TUMO TIRANA')->firstOrFail();
    $worker = User::factory()->operational()->create(['tenant_id' => $tumo->id]);
    $worker->syncRoles(RoleName::Operations);

    $response = $this->actingAs($worker)
        ->getJson(route('operations.spaces.index', ['per_page' => 100]))
        ->assertOk();

    $spaces = collect($response->json('data'));

    expect($spaces)->not->toBeEmpty();
    expect($spaces->every(fn (array $space): bool => Space::find($space['id'])?->tenant_id === $tumo->id))->toBeTrue();
    expect(
        Space::query()->where('tenant_id', $tumo->id)->count(),
    )->toBe($spaces->count());
});

test('ict workers only see ict tenant spaces', function (): void {
    $ict = Tenant::query()->where('title', 'ICT ECOSYSTEM')->firstOrFail();
    $worker = User::factory()->operational()->create(['tenant_id' => $ict->id]);
    $worker->syncRoles(RoleName::Operations);

    $response = $this->actingAs($worker)
        ->getJson(route('operations.spaces.index', ['per_page' => 100]))
        ->assertOk();

    $spaces = collect($response->json('data'));

    expect($spaces)->not->toBeEmpty();
    expect($spaces->every(fn (array $space): bool => Space::find($space['id'])?->tenant_id === $ict->id))->toBeTrue();
});

test('workers cannot view spaces belonging to another tenant', function (): void {
    $tumo = Tenant::query()->where('title', 'TUMO TIRANA')->firstOrFail();
    $arts = Tenant::query()->where('title', 'ARTS')->firstOrFail();
    $worker = User::factory()->operational()->create(['tenant_id' => $tumo->id]);
    $worker->syncRoles(RoleName::Operations);

    $foreignSpace = Space::query()->where('tenant_id', $arts->id)->firstOrFail();

    $this->actingAs($worker)
        ->getJson(route('operations.spaces.show', $foreignSpace))
        ->assertForbidden();
});
