<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'label', 'description'])]
class Role extends Model
{
    /**
     * The permissions granted by this role.
     *
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * The users assigned this role.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Whether this role grants the given permission name.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions->contains('name', $permission);
    }

    /**
     * Sync the given permission names onto this role (by name).
     *
     * @param  list<string>  $permissionNames
     */
    public function syncPermissionsByName(array $permissionNames): void
    {
        $ids = Permission::query()
            ->whereIn('name', $permissionNames)
            ->pluck('id');

        $this->permissions()->sync($ids);
    }
}
