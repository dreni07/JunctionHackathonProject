<?php

declare(strict_types=1);

namespace App\Agent;

use App\Agent\Tools\TaskApiTool;
use App\Agent\Tools\WorkforceQueryTool;
use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\Event;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\OpenAiChatService;
use Illuminate\Support\Collection;

/**
 * After an event is approved, this agent breaks the whole event down into
 * concrete operational tasks and assigns each one to the tenant's workers by
 * their role — using a db_query tool to inspect the workforce and an api_tool
 * to create and assign every task.
 *
 * @phpstan-type PlannedTask array<string, mixed>
 */
class TaskPlanningAgent
{
    private const MAX_STEPS = 8;

    public function __construct(
        private readonly OpenAiChatService $llm,
        private readonly ActivityLogService $activityLog,
    ) {}

    /**
     * Plan and assign every task for an event.
     *
     * @return array{tasks: list<PlannedTask>, summary: string, workers: int, tools_used: list<string>}
     */
    public function planFor(Event $event, User $actor): array
    {
        $workers = $this->tenantWorkers($actor);

        if ($workers->isEmpty()) {
            return ['tasks' => [], 'summary' => 'No operational workers are available in this branch to assign tasks to.', 'workers' => 0, 'tools_used' => []];
        }

        $apiTool = new TaskApiTool($event, $actor, $workers, $this->activityLog);

        /** @var array<string, Tool> $tools */
        $tools = [];
        foreach ([new WorkforceQueryTool, $apiTool] as $tool) {
            $tools[$tool->name()] = $tool;
        }

        /** @var list<array<string, mixed>> $messages */
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($event, $actor, $workers)],
            ['role' => 'user', 'content' => 'Plan and assign all the tasks for this event now.'],
        ];

        $definitions = $this->toolDefinitions($tools);
        $toolsUsed = [];
        $reply = '';

        for ($step = 0; $step < self::MAX_STEPS; $step++) {
            $message = $this->llm->chatWithTools($messages, $definitions);
            $messages[] = $message;

            $toolCalls = $message['tool_calls'] ?? null;

            if (! is_array($toolCalls) || $toolCalls === []) {
                $reply = is_string($message['content'] ?? null) ? $message['content'] : '';
                break;
            }

            foreach ($toolCalls as $toolCall) {
                if (! is_array($toolCall)) {
                    continue;
                }

                $name = is_string($toolCall['function']['name'] ?? null) ? $toolCall['function']['name'] : '';
                $toolsUsed[] = $name;

                $decoded = json_decode((string) ($toolCall['function']['arguments'] ?? '{}'), true);
                $arguments = is_array($decoded) ? $decoded : [];

                $tool = $tools[$name] ?? null;
                $result = $tool !== null ? $tool->execute($arguments) : "Unknown tool: {$name}";

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => is_string($toolCall['id'] ?? null) ? $toolCall['id'] : '',
                    'content' => $result,
                ];
            }
        }

        return [
            'tasks' => $apiTool->created(),
            'summary' => trim($reply) ?: 'Tasks planned and assigned.',
            'workers' => $workers->count(),
            'tools_used' => array_values(array_unique($toolsUsed)),
        ];
    }

    /**
     * The operational workers of the acting worker's tenant — the only people
     * a task may be assigned to.
     *
     * @return Collection<int, User>
     */
    private function tenantWorkers(User $actor): Collection
    {
        if ($actor->tenant_id === null) {
            return collect();
        }

        return User::query()
            ->where('tenant_id', $actor->tenant_id)
            ->where('account_type', AccountType::Operational->value)
            ->whereHas('roles', fn ($q) => $q->where('name', RoleName::Operations->value))
            ->orderBy('worker_role')
            ->get(['id', 'name', 'worker_role', 'tenant_id', 'account_type']);
    }

    /**
     * @param  array<string, Tool>  $tools
     * @return list<array<string, mixed>>
     */
    private function toolDefinitions(array $tools): array
    {
        return array_values(array_map(
            fn (Tool $tool): array => [
                'type' => 'function',
                'function' => [
                    'name' => $tool->name(),
                    'description' => $tool->description(),
                    'parameters' => $tool->parameters(),
                ],
            ],
            $tools,
        ));
    }

    /**
     * @param  Collection<int, User>  $workers
     */
    private function systemPrompt(Event $event, User $actor, Collection $workers): string
    {
        $attendees = (int) ($event->attendees ?? 0);
        $suggested = max(5, min(14, (int) ceil($attendees / 15) + 4));

        $eventType = $event->event_type instanceof \BackedEnum ? $event->event_type->value : (string) $event->event_type;
        $venue = $event->relationLoaded('eventRequest') ? $event->eventRequest?->matchedSpace?->name : null;
        $start = $event->start_time?->toDayDateTimeString() ?? 'to be confirmed';
        $end = $event->end_time?->toDayDateTimeString() ?? 'to be confirmed';

        $roster = $workers
            ->map(fn (User $w): string => "  - #{$w->id} {$w->name} — ".($w->worker_role ?: 'worker'))
            ->implode("\n");

        return <<<PROMPT
            You are the Pyramid's operations planner. An event has just been approved, and you must break the
            WHOLE event down into concrete operational tasks and assign each one to the right worker.

            EVENT
              - Title: {$event->title}
              - Type: {$eventType}
              - Expected attendees: {$attendees}
              - Venue: {$venue}
              - Starts: {$start}
              - Ends: {$end}

            THE TEAM (tenant #{$actor->tenant_id} — assign ONLY to these workers, by their role):
            {$roster}

            You may also call db_query to inspect the workforce, e.g.
            SELECT id, name, worker_role FROM users WHERE tenant_id = {$actor->tenant_id} AND account_type = 'operational'.

            HOW TO PLAN
              - Cover the whole event across three phases: setup (before), during (the live event), teardown (after).
              - Create a sensible set of tasks that together cover everything — around {$suggested} for an event this
                size. No gaps, no busywork.
              - Every task must be DISTINCT — never repeat the same task. Make sure to include during-event and
                teardown tasks too, not only setup.
              - Assign each task to the worker whose role best fits it (a technician handles AV and tech; front-desk
                or coordinator staff handle registration and guests; setup/management staff handle the room and
                logistics, etc.). Spread the work fairly — do not pile everything on one person.
              - For EACH task, call api_tool with action "create_task": give a clear name, a short description, the
                phase, the worker_id, and due_offset_hours relative to the event start (negative = before the event,
                positive = after it ends).
              - Create ALL the tasks by calling api_tool repeatedly. When every task is created, reply with a single
                short sentence summarising the plan. Do NOT ask questions — just act.
            PROMPT;
    }
}
