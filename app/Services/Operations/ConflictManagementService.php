<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Authorization\Permissions;
use App\Enums\ActivityLogAction;
use App\Enums\ConflictStatus;
use App\Models\Conflict;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Support\OperationalAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class ConflictManagementService
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    /**
     * @param  array{status?: string}  $filters
     * @return LengthAwarePaginator<int, Conflict>
     */
    public function paginate(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        if (! $user->hasPermissionTo(Permissions::CONFLICTS_VIEW)) {
            throw new InvalidArgumentException('You are not allowed to view conflicts.');
        }

        $query = Conflict::query()
            ->with(['space:id,name', 'event:id,title', 'eventRequest:id,title'])
            ->latest('detected_at');

        if (! OperationalAccess::managesRequests($user)) {
            $query->whereHas(
                'eventRequest',
                fn ($q) => OperationalAccess::scopeEventRequests($q, $user),
            );
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function find(User $user, Conflict $conflict): Conflict
    {
        if (! $user->hasPermissionTo(Permissions::CONFLICTS_VIEW)) {
            throw new InvalidArgumentException('You are not allowed to view conflicts.');
        }

        return $conflict->load(['space:id,name', 'event:id,title', 'eventRequest:id,title', 'resolver:id,name']);
    }

    /**
     * @param  array{notes?: string|null}  $data
     */
    public function resolve(User $user, Conflict $conflict, array $data = []): Conflict
    {
        if (! $user->hasPermissionTo(Permissions::REQUESTS_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to resolve conflicts.');
        }

        $conflict->update([
            'status' => ConflictStatus::Resolved->value,
            'resolved_at' => now(),
            'resolved_by' => $user->id,
            'description' => trim(($conflict->description ?? '')."\n\nResolved: ".($data['notes'] ?? '')),
        ]);

        $this->activityLog->record(
            ActivityLogAction::ConflictResolved,
            "Conflict resolved: {$conflict->title}",
            $user,
            $conflict->eventRequest,
            $conflict->event,
            properties: ['conflict_id' => $conflict->id, 'notes' => $data['notes'] ?? null],
        );

        return $conflict->fresh(['space:id,name', 'resolver:id,name']);
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Conflict $conflict): array
    {
        return [
            'id' => $conflict->id,
            'title' => $conflict->title,
            'description' => $conflict->description,
            'type' => $conflict->type->value,
            'type_label' => $conflict->type->label(),
            'status' => $conflict->status->value,
            'status_label' => $conflict->status->label(),
            'severity' => $conflict->severity->value,
            'detected_at' => $conflict->detected_at->toIso8601String(),
            'resolved_at' => $conflict->resolved_at?->toIso8601String(),
            'event_id' => $conflict->event_id,
            'event_request_id' => $conflict->event_request_id,
            'space_id' => $conflict->space_id,
            'space' => $conflict->relationLoaded('space') ? $conflict->space?->only(['id', 'name']) : null,
        ];
    }
}
