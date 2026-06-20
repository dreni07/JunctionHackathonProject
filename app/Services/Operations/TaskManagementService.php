<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Authorization\Permissions;
use App\Enums\ActivityLogAction;
use App\Enums\TaskPhase;
use App\Enums\TaskState;
use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Support\OperationalAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class TaskManagementService
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    /**
     * @param  array{event_id?: string, state?: string, phase?: string}  $filters
     * @return LengthAwarePaginator<int, Task>
     */
    public function paginate(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        if (! $user->hasPermissionTo(Permissions::TASKS_VIEW)) {
            throw new InvalidArgumentException('You are not allowed to view tasks.');
        }

        $query = Task::query()
            ->with(['event:id,title', 'worker:id,name'])
            ->latest();

        if (! OperationalAccess::managesEvents($user)) {
            $query->whereHas('event', fn ($q) => OperationalAccess::scopeEvents($q, $user));
        }

        if (! empty($filters['event_id'])) {
            $query->where('event_id', $filters['event_id']);
        }

        if (! empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        if (! empty($filters['phase'])) {
            $query->where('phase', $filters['phase']);
        }

        return $query->paginate($perPage);
    }

    public function find(User $user, Task $task): Task
    {
        $task->load('event');
        OperationalAccess::ensureCanViewEvent($user, $task->event);

        return $task->load(['worker:id,name,email']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Task
    {
        if (! $user->hasPermissionTo(Permissions::TASKS_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to create tasks.');
        }

        $event = Event::query()->findOrFail($data['event_id']);
        OperationalAccess::ensureCanManageEvent($user, $event);

        $phase = TaskPhase::tryFrom((string) $data['phase']);
        if (! $phase instanceof TaskPhase) {
            throw new InvalidArgumentException('Invalid task phase.');
        }

        $task = Task::query()->create([
            'event_id' => $event->id,
            'organization_id' => $event->organization_id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'phase' => $phase->value,
            'state' => TaskState::Pending->value,
            'due_at' => $data['due_at'] ?? null,
        ]);

        if (! empty($data['user_id'])) {
            $worker = User::query()->findOrFail((int) $data['user_id']);
            $task->assignTo($worker);
            $this->activityLog->record(
                ActivityLogAction::TaskAssigned,
                "Task assigned to {$worker->name}",
                $user,
                event: $event,
                properties: ['task_id' => $task->id, 'worker_id' => $worker->id],
            );
        }

        $this->activityLog->record(
            ActivityLogAction::Created,
            "Task created: {$task->name}",
            $user,
            event: $event,
            properties: ['task_id' => $task->id],
        );

        return $task->load(['event:id,title', 'worker:id,name']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, Task $task, array $data): Task
    {
        if (! $user->hasPermissionTo(Permissions::TASKS_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to update tasks.');
        }

        $task->load('event');
        OperationalAccess::ensureCanManageEvent($user, $task->event);

        $payload = [];

        foreach (['name', 'description', 'due_at'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (array_key_exists('phase', $data)) {
            $phase = TaskPhase::tryFrom((string) $data['phase']);
            if (! $phase instanceof TaskPhase) {
                throw new InvalidArgumentException('Invalid task phase.');
            }
            $payload['phase'] = $phase->value;
        }

        if (array_key_exists('state', $data)) {
            $state = TaskState::tryFrom((string) $data['state']);
            if (! $state instanceof TaskState) {
                throw new InvalidArgumentException('Invalid task state.');
            }
            $payload['state'] = $state->value;
        }

        $task->update($payload);

        if (array_key_exists('user_id', $data) && $data['user_id'] !== null) {
            $worker = User::query()->findOrFail((int) $data['user_id']);
            $task->assignTo($worker);
        }

        $this->activityLog->record(
            ActivityLogAction::Updated,
            "Task updated: {$task->name}",
            $user,
            event: $task->event,
            properties: ['task_id' => $task->id],
        );

        return $task->fresh(['event:id,title', 'worker:id,name']);
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Task $task): array
    {
        return [
            'id' => $task->id,
            'event_id' => $task->event_id,
            'name' => $task->name,
            'description' => $task->description,
            'state' => $task->state->value,
            'state_label' => $task->state->label(),
            'phase' => $task->phase->value,
            'phase_label' => $task->phase->label(),
            'due_at' => $task->due_at?->toIso8601String(),
            'user_id' => $task->user_id,
            'worker' => $task->relationLoaded('worker') ? $task->worker?->only(['id', 'name']) : null,
            'event' => $task->relationLoaded('event') ? $task->event?->only(['id', 'title']) : null,
        ];
    }
}
