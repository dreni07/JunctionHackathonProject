<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Enums\TenantWorkerRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'account_type' => AccountType::Organization->value,
            'tenant_id' => null,
            'worker_role' => null,
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * External organization account (planner / public sign-up).
     */
    public function organization(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::Organization->value,
            'tenant_id' => null,
            'worker_role' => null,
        ]);
    }

    /**
     * Tenant-based Pyramid operational worker.
     */
    public function operational(): static
    {
        return $this->state(function (array $attributes): array {
            $tenant = Tenant::query()->first() ?? Tenant::query()->create([
                'title' => 'Test Branch',
                'description' => 'Factory tenant',
                'roles' => [TenantWorkerRole::Manager->value, 'Technician'],
            ]);

            return [
                'account_type' => AccountType::Operational->value,
                'tenant_id' => $tenant->id,
                'worker_role' => 'Technician',
                'organization_id' => null,
            ];
        });
    }

    /**
     * Tenant manager for a Pyramid branch.
     */
    public function tenantManager(): static
    {
        return $this->operational()->state(fn (array $attributes): array => [
            'worker_role' => TenantWorkerRole::Manager->value,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
