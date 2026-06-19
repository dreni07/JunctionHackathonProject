<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property AccountType|null $account_type
 * @property int|null $tenant_id
 * @property string|null $worker_role
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'organization_id', 'account_type', 'tenant_id', 'worker_role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'account_type' => AccountType::class,
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * The roles assigned to this user.
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * The organization this user belongs to.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The tenant (Pyramid branch) an operational worker signs into.
     *
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Whether this account signs in through the organization door.
     */
    public function isOrganization(): bool
    {
        return $this->account_type === AccountType::Organization;
    }

    /**
     * Whether this account is a tenant-based operational worker.
     */
    public function isOperational(): bool
    {
        return $this->account_type === AccountType::Operational;
    }

    /**
     * Events this user has created.
     *
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Operational tasks assigned to this user.
     *
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Operational alerts targeted at this user (null user_id = team-wide).
     *
     * @return HasMany<Alert, $this>
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Whether this user is a Pyramid operational worker — the only kind of
     * user a task may be assigned to.
     */
    public function isOperationalWorker(): bool
    {
        return $this->hasRole(RoleName::Operations);
    }

    /**
     * Scope to the operational workers a task can be assigned to.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeOperationalWorkers(Builder $query): Builder
    {
        return $query->whereHas('roles', fn ($q) => $q->where('name', RoleName::Operations->value));
    }

    /**
     * Whether the user has the given role.
     */
    public function hasRole(RoleName|string $role): bool
    {
        $name = $role instanceof RoleName ? $role->value : $role;

        return $this->roles->contains('name', $name);
    }

    /**
     * Assign one or more roles to the user (by enum or machine name),
     * without detaching existing roles.
     */
    public function assignRole(RoleName|string ...$roles): void
    {
        $names = array_map(
            fn (RoleName|string $role): string => $role instanceof RoleName ? $role->value : $role,
            $roles,
        );

        $ids = Role::query()->whereIn('name', $names)->pluck('id');

        $this->roles()->syncWithoutDetaching($ids);
        $this->unsetRelation('roles');
    }

    /**
     * Whether the user has the given permission through any of their roles.
     * This is what backs every `can:` gate check on routes.
     */
    public function hasPermissionTo(string $permission): bool
    {
        return $this->permissionNames()->contains($permission);
    }

    /**
     * The flat list of permission names granted by all of the user's roles.
     * Shared to the frontend so React can show/hide UI (routing stays gated
     * server-side via permissions, never via role checks).
     *
     * @return Collection<int, string>
     */
    public function permissionNames(): Collection
    {
        return $this->loadMissing('roles.permissions')
            ->roles
            ->flatMap(fn (Role $role): iterable => $role->permissions->pluck('name'))
            ->unique()
            ->values();
    }
}
