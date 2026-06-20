<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Authorization\Permissions;
use App\Models\Space;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class SpaceCatalogService
{
    /**
     * @param  array{zone?: string, search?: string}  $filters
     * @return LengthAwarePaginator<int, Space>
     */
    public function paginate(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        if (! $user->hasPermissionTo(Permissions::SPACES_VIEW)) {
            throw new InvalidArgumentException('You are not allowed to view spaces.');
        }

        $query = Space::query()->orderBy('zone_class')->orderBy('name');

        if ($user->isOperational() && $user->tenant_id !== null) {
            $query->where('tenant_id', $user->tenant_id);
        }

        if (! empty($filters['zone'])) {
            $query->where('zone_class', $filters['zone']);
        }

        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where('name', 'like', $term);
        }

        return $query->paginate($perPage);
    }

    public function find(User $user, Space $space): Space
    {
        if (! $user->hasPermissionTo(Permissions::SPACES_VIEW)) {
            throw new InvalidArgumentException('You are not allowed to view spaces.');
        }

        if ($user->isOperational() && $user->tenant_id !== null && $space->tenant_id !== $user->tenant_id) {
            throw new InvalidArgumentException('You are not allowed to view this space.');
        }

        return $space->loadCount('reservations');
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Space $space): array
    {
        return [
            'id' => $space->id,
            'name' => $space->name,
            'room_code' => $space->room_code,
            'zone_class' => $space->zone_class,
            'floor' => $space->floor,
            'capacity' => $space->capacity,
            'type' => $space->type->value,
            'type_label' => $space->type->label(),
            'functional_type' => $space->functional_type,
            'area_sqm' => $space->area_sqm,
            'features' => $space->features,
            'reservations_count' => $space->reservations_count ?? null,
        ];
    }
}
