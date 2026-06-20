<?php

declare(strict_types=1);

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Enums\TenantWorkerRole;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TenantManagerSeeder;
use Database\Seeders\TenantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('tenant manager seeder creates one verified manager per tenant', function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(TenantSeeder::class);
    $this->seed(TenantManagerSeeder::class);

    $tenants = Tenant::query()->orderBy('id')->get();

    expect($tenants)->not->toBeEmpty();

    foreach ($tenants as $tenant) {
        $manager = User::query()
            ->where('tenant_id', $tenant->id)
            ->where('worker_role', TenantWorkerRole::Manager->value)
            ->first();

        expect($manager)->not->toBeNull();
        expect($manager->account_type)->toBe(AccountType::Operational);
        expect($manager->email_verified_at)->not->toBeNull();
        expect($manager->hasRole(RoleName::Operations))->toBeTrue();
        expect(Hash::check('password', $manager->password))->toBeTrue();
        expect($manager->isTenantManager())->toBeTrue();
    }
});
