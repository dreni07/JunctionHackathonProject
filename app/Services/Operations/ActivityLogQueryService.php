<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Authorization\Permissions;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class ActivityLogQueryService
{
    /**
     * @param  array{event_id?: string, event_request_id?: string, final_proposal_id?: string}  $filters
     * @return LengthAwarePaginator<int, ActivityLog>
     */
    public function paginate(User $user, array $filters = [], int $perPage = 30): LengthAwarePaginator
    {
        if (! $user->hasPermissionTo(Permissions::ACTIVITY_VIEW)) {
            throw new InvalidArgumentException('You are not allowed to view activity logs.');
        }

        $query = ActivityLog::query()->with('user:id,name')->latest();

        foreach (['event_id', 'event_request_id', 'final_proposal_id'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        return $query->paginate($perPage);
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(ActivityLog $log): array
    {
        return [
            'id' => $log->id,
            'action' => $log->action->value,
            'action_label' => $log->action->label(),
            'description' => $log->description,
            'properties' => $log->properties,
            'user' => $log->relationLoaded('user') ? $log->user?->only(['id', 'name']) : null,
            'event_id' => $log->event_id,
            'event_request_id' => $log->event_request_id,
            'final_proposal_id' => $log->final_proposal_id,
            'created_at' => $log->created_at?->toIso8601String(),
        ];
    }
}
