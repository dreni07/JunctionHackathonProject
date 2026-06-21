<?php

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Enums\TenantWorkerRole;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TenantSeeder;
use Database\Seeders\WorkforceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('workforce seeder creates fifty verified operational workers per tenant', function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(TenantSeeder::class);
    $this->seed(WorkforceSeeder::class);

    $tenants = Tenant::query()->orderBy('id')->get();

    expect($tenants)->not->toBeEmpty();

    foreach ($tenants as $tenant) {
        $workers = User::query()
            ->where('tenant_id', $tenant->id)
            ->where('account_type', AccountType::Operational->value)
            ->where('email', 'like', '%-worker-%@pyramid.test')
            ->get();

        expect($workers)->toHaveCount(50);

        foreach ($workers as $worker) {
            expect($worker->email_verified_at)->not->toBeNull();
            expect($worker->hasRole(RoleName::Operations))->toBeTrue();
            expect(Hash::check('password', $worker->password))->toBeTrue();
            expect($worker->worker_role)->toBeIn($tenant->roles);
            expect($worker->worker_role)->not->toBe(TenantWorkerRole::Manager->value);
        }
    }
});

test('workforce seeder can be re-run without duplicate emails', function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(TenantSeeder::class);

    $this->seed(WorkforceSeeder::class);
    $this->seed(WorkforceSeeder::class);

    expect(
        User::query()
            ->where('email', 'like', '%-worker-%@pyramid.test')
            ->count(),
    )->toBe(150);
});
