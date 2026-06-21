<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Authorization\Permissions;
use App\Enums\AssetStatus;
use App\Enums\AssetType;
use App\Enums\ReservationStatus;
use App\Models\Asset;
use App\Models\AssetReservation;
use App\Models\Event;
use App\Models\User;
use App\Support\OperationalAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class AssetManagementService
{
    /**
     * @return LengthAwarePaginator<int, Asset>
     */
    public function paginate(User $user, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        if (! $user->hasPermissionTo(Permissions::INVENTORY_VIEW)) {
            throw new InvalidArgumentException('You are not allowed to view inventory.');
        }

        $query = Asset::query()->orderBy('name');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->paginate($perPage);
    }

    public function find(User $user, Asset $asset): Asset
    {
        if (! $user->hasPermissionTo(Permissions::INVENTORY_VIEW)) {
            throw new InvalidArgumentException('You are not allowed to view inventory.');
        }

        return $asset->load(['assignedEvent:id,title', 'reservations.event:id,title']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, Asset $asset, array $data): Asset
    {
        if (! $user->hasPermissionTo(Permissions::INVENTORY_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to manage inventory.');
        }

        $payload = [];

        if (array_key_exists('name', $data)) {
            $payload['name'] = $data['name'];
        }

        if (array_key_exists('current_location', $data)) {
            $payload['current_location'] = $data['current_location'];
        }

        if (array_key_exists('status', $data)) {
            $status = AssetStatus::tryFrom((string) $data['status']);
            if (! $status instanceof AssetStatus) {
                throw new InvalidArgumentException('Invalid asset status.');
            }
            $payload['status'] = $status->value;
        }

        if (array_key_exists('type', $data)) {
            $type = AssetType::tryFrom((string) $data['type']);
            if (! $type instanceof AssetType) {
                throw new InvalidArgumentException('Invalid asset type.');
            }
            $payload['type'] = $type->value;
        }

        if (array_key_exists('assigned_event_id', $data)) {
            $payload['assigned_event_id'] = $data['assigned_event_id'];
        }

        $asset->update($payload);

        return $asset->fresh(['assignedEvent:id,title']);
    }

    /**
     * @param  array{asset_id: string, reserved_quantity: int}  $data
     */
    public function reserveForEvent(User $user, Event $event, array $data): AssetReservation
    {
        if (! $user->hasPermissionTo(Permissions::INVENTORY_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to reserve assets.');
        }

        OperationalAccess::ensureCanManageEvent($user, $event);

        return AssetReservation::query()->create([
            'asset_id' => $data['asset_id'],
            'event_id' => $event->id,
            'reserved_quantity' => $data['reserved_quantity'],
            'status' => ReservationStatus::Reserved->value,
        ]);
    }

    public function releaseReservation(User $user, AssetReservation $reservation): void
    {
        if (! $user->hasPermissionTo(Permissions::INVENTORY_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to release asset reservations.');
        }

        $event = $reservation->event;
        OperationalAccess::ensureCanManageEvent($user, $event);
        $reservation->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Asset $asset): array
    {
        return [
            'id' => $asset->id,
            'name' => $asset->name,
            'type' => $asset->type->value,
            'type_label' => $asset->type->label(),
            'status' => $asset->status->value,
            'status_label' => $asset->status->label(),
            'qr_code' => $asset->qr_code,
            'current_location' => $asset->current_location,
            'assigned_event_id' => $asset->assigned_event_id,
        ];
    }
}
