<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TenantWorkerRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property array<int, string> $roles
 */
class Tenant extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'description',
        'roles',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'roles' => 'array',
        ];
    }

    /**
     * The operational workers that belong to this branch.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Worker roles a tenant manager may assign when provisioning staff.
     * The Manager role itself is reserved and cannot be self-assigned.
     *
     * @return list<string>
     */
    public function assignableWorkerRoles(): array
    {
        return array_values(array_filter(
            $this->roles ?? [],
            fn (string $role): bool => $role !== TenantWorkerRole::Manager->value,
        ));
    }

    /**
     * Which Pyramid branch owns a venue based on its zone and function.
     */
    public static function resolveSpaceTenantId(string $zoneClass, string $functionalType): ?int
    {
        static $tenantIds = null;

        $tenantIds ??= self::query()->pluck('id', 'title');

        if ($zoneClass === 'TUMO') {
            return $tenantIds['TUMO TIRANA'] ?? null;
        }

        return match ($functionalType) {
            'Tech Lab', 'Incubator', 'Startup Office' => $tenantIds['ICT ECOSYSTEM'] ?? null,
            default => $tenantIds['ARTS'] ?? null,
        };
    }

    /**
     * Venues operated by this branch.
     *
     * @return HasMany<Space, $this>
     */
    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }
}
