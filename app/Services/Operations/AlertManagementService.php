<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Authorization\Permissions;
use App\Models\Alert;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class AlertManagementService
{
    /**
     * @return LengthAwarePaginator<int, Alert>
     */
    public function paginate(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Alert::query()->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! $user->hasPermissionTo(Permissions::REQUESTS_MANAGE)) {
            $query->where(function ($scoped) use ($user): void {
                $scoped->where('user_id', $user->id)->orWhereNull('user_id');
            });
        }

        return $query->paginate($perPage);
    }

    public function markAsRead(User $user, Alert $alert): Alert
    {
        if ($alert->user_id !== null && $alert->user_id !== $user->id && ! $user->hasPermissionTo(Permissions::REQUESTS_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to update this alert.');
        }

        $alert->markAsRead();

        return $alert->fresh();
    }

    public function dismiss(User $user, Alert $alert): Alert
    {
        if ($alert->user_id !== null && $alert->user_id !== $user->id && ! $user->hasPermissionTo(Permissions::REQUESTS_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to dismiss this alert.');
        }

        $alert->dismiss();

        return $alert->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Alert $alert): array
    {
        return [
            'id' => $alert->id,
            'title' => $alert->title,
            'message' => $alert->message,
            'source' => $alert->source->value,
            'severity' => $alert->severity->value,
            'status' => $alert->status->value,
            'agent_name' => $alert->agent_name,
            'event_id' => $alert->event_id,
            'event_request_id' => $alert->event_request_id,
            'read_at' => $alert->read_at?->toIso8601String(),
            'created_at' => $alert->created_at?->toIso8601String(),
        ];
    }
}
