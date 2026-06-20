<?php

declare(strict_types=1);

namespace App\Agent\Tools;

use App\Agent\Tool;
use App\Enums\ActivityLogAction;
use App\Enums\TaskPhase;
use App\Enums\TaskState;
use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use App\Services\ActivityLogService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Throwable;

/**
 * The internal API the task-planning agent uses to create and assign tasks for
 * an event. Each call creates one task, assigns it to a worker of the tenant,
 * and records it so the agent's full plan can be returned to the dashboard.
 */
class TaskApiTool implements Tool
{
    /** @var list<array<string, mixed>> */
    private array $created = [];

    /**
     * @param  Collection<int, User>  $workers  Assignable workers keyed by id.
     */
    public function __construct(
        private readonly Event $event,
        private readonly User $actor,
        private readonly Collection $workers,
        private readonly ActivityLogService $activityLog,
    ) {}

    public function name(): string
    {
        return 'api_tool';
    }

    public function description(): string
    {
        return 'Create and assign ONE operational task for this event. Call it once per task — call it '
            .'many times to build the full plan. Assign each task to the worker (by worker_id) whose role '
            .'best fits the work. phase is when it happens: setup (before), during (live event), teardown (after). '
            .'due_offset_hours is relative to the event START (negative = before the event, positive = after).';
    }

    /**
     * @return array<string, mixed>
     */
    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['create_task'],
                    'description' => 'Always "create_task".',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Short, concrete task title, e.g. "Set up AV and microphones in the hall".',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'One or two sentences of detail for the worker.',
                ],
                'phase' => [
                    'type' => 'string',
                    'enum' => ['setup', 'during', 'teardown'],
                ],
                'worker_id' => [
                    'type' => 'integer',
                    'description' => 'The id of the worker to assign — must be one of the provided workers.',
                ],
                'due_offset_hours' => [
                    'type' => 'number',
                    'description' => 'Hours relative to the event start (negative = before, positive = after).',
                ],
            ],
            'required' => ['action', 'name', 'phase', 'worker_id'],
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function execute(array $arguments): string
    {
        $workerId = (int) ($arguments['worker_id'] ?? 0);
        $worker = $this->workers->firstWhere('id', $workerId);

        if (! $worker instanceof User) {
            return 'Error: worker_id '.$workerId.' is not one of this tenant\'s workers. Valid workers: '
                .$this->workers->map(fn (User $w): string => $w->id.' ('.($w->worker_role ?? 'worker').')')->implode(', ').'.';
        }

        $phase = TaskPhase::tryFrom((string) ($arguments['phase'] ?? ''));

        if (! $phase instanceof TaskPhase) {
            return 'Error: phase must be one of setup, during, teardown.';
        }

        $name = trim((string) ($arguments['name'] ?? ''));

        if ($name === '') {
            return 'Error: a task name is required.';
        }

        foreach ($this->created as $existing) {
            if (strcasecmp((string) $existing['name'], $name) === 0) {
                return 'Skipped: a task named “'.$name.'” already exists. Create a DIFFERENT, distinct task '
                    .'(cover during-event and teardown work too), or finish if the plan is complete.';
            }
        }

        try {
            $task = Task::query()->create([
                'event_id' => $this->event->id,
                'organization_id' => $this->event->organization_id,
                'name' => $name,
                'description' => trim((string) ($arguments['description'] ?? '')) ?: null,
                'phase' => $phase->value,
                'state' => TaskState::Pending->value,
                'due_at' => $this->dueAt($arguments['due_offset_hours'] ?? null),
            ]);

            $task->assignTo($worker);
        } catch (Throwable $e) {
            return 'Error creating task: '.$e->getMessage();
        }

        $this->activityLog->record(
            ActivityLogAction::TaskAssigned,
            "Task “{$task->name}” assigned to {$worker->name}",
            $this->actor,
            event: $this->event,
            properties: ['task_id' => $task->id, 'worker_id' => $worker->id, 'by' => 'task_planning_agent'],
        );

        $this->created[] = [
            'id' => $task->id,
            'name' => $task->name,
            'description' => $task->description,
            'phase' => $task->phase->value,
            'phase_label' => $task->phase->label(),
            'state' => $task->state->value,
            'due_at' => $task->due_at?->toIso8601String(),
            'worker' => ['id' => $worker->id, 'name' => $worker->name, 'role' => $worker->worker_role],
        ];

        return "Created and assigned “{$task->name}” to {$worker->name} ({$worker->worker_role}).";
    }

    /**
     * The tasks created during this planning run.
     *
     * @return list<array<string, mixed>>
     */
    public function created(): array
    {
        return $this->created;
    }

    private function dueAt(mixed $offsetHours): ?CarbonInterface
    {
        if ($this->event->start_time === null || ! is_numeric($offsetHours)) {
            return $this->event->start_time;
        }

        return $this->event->start_time->copy()->addMinutes((int) round((float) $offsetHours * 60));
    }
}
