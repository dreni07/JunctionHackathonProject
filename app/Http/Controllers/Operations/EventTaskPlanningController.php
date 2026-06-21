<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Agent\TaskPlanningAgent;
use App\Authorization\Permissions;
use App\Models\Event;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Runs the task-planning agent for an approved event: it splits the event into
 * tasks and assigns them to the tenant's workers by role.
 */
class EventTaskPlanningController extends OperationsController
{
    public function __construct(private readonly TaskPlanningAgent $agent) {}

    public function store(Request $request, Event $event): JsonResponse
    {
        return $this->handleOperation(function () use ($request, $event): JsonResponse {
            $this->authorize(Permissions::TASKS_MANAGE);

            // The AI planning can take a while — never let PHP's limit kill it.
            @set_time_limit(0);

            $force = $request->boolean('force');

            // Don't silently double-plan: if tasks already exist, return them
            // unless an explicit re-plan was requested.
            if (! $force && $event->tasks()->exists()) {
                return $this->json([
                    'already_planned' => true,
                    'summary' => 'Tasks have already been planned for this event.',
                    'tasks' => $this->existingTasks($event),
                ]);
            }

            $event->load('eventRequest.matchedSpace:id,name');

            $result = $this->agent->planFor($event, $request->user());

            return $this->json([
                'already_planned' => false,
                'summary' => $result['summary'],
                'workers' => $result['workers'],
                'tools_used' => $result['tools_used'],
                'tasks' => $result['tasks'],
            ], 201);
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function existingTasks(Event $event): array
    {
        return $event->tasks()
            ->with('worker:id,name,worker_role')
            ->get()
            ->map(fn (Task $task): array => [
                'id' => $task->id,
                'name' => $task->name,
                'description' => $task->description,
                'phase' => $task->phase->value,
                'phase_label' => $task->phase->label(),
                'state' => $task->state->value,
                'due_at' => $task->due_at?->toIso8601String(),
                'worker' => $task->worker
                    ? ['id' => $task->worker->id, 'name' => $task->worker->name, 'role' => $task->worker->worker_role]
                    : null,
            ])
            ->all();
    }
}
