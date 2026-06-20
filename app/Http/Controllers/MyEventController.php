<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The organization-facing "My events" experience: every event they have
 * booked with the Pyramid, split into upcoming and finished, with portfolio
 * analytics, plus a per-event viewer showing live task readiness.
 */
class MyEventController extends Controller
{
    /** How much each task state counts toward an event being "ready" (0–1). */
    private const READINESS_WEIGHTS = [
        'pending' => 0.0,
        'started' => 0.25,
        'ongoing' => 0.5,
        'on_process' => 0.8,
        'finished' => 1.0,
    ];

    /**
     * The events list + portfolio analytics.
     */
    public function index(Request $request): Response
    {
        $events = $this->scopedEvents($request->user())
            ->with(['eventRequest:id,price_agreed,price_suggested,matched_space_id', 'eventRequest.matchedSpace:id,name,zone_class,floor'])
            ->withCount(['tasks', 'tasks as finished_tasks_count' => fn (Builder $q) => $q->where('state', 'finished')])
            ->orderByDesc('start_time')
            ->get();

        $serialized = $events->map(fn (Event $event): array => $this->serializeCard($event));

        return Inertia::render('my-events/index', [
            'upcoming' => $serialized->filter(fn (array $e): bool => ! $e['is_finished'])->values()->all(),
            'finished' => $serialized->filter(fn (array $e): bool => $e['is_finished'])->values()->all(),
            'stats' => $this->stats($events),
        ]);
    }

    /**
     * The single-event viewer.
     */
    public function show(Request $request, Event $event): Response
    {
        $this->authorizeEvent($request->user(), $event);

        $event->load([
            'eventRequest:id,price_agreed,price_suggested,description,matched_space_id',
            'eventRequest.matchedSpace:id,name,zone_class,floor,capacity,box_ref,location_geometry',
        ]);

        return Inertia::render('my-events/show', [
            'event' => $this->serializeDetail($event),
            'progress' => $this->progressFor($event),
        ]);
    }

    /**
     * Live task-readiness snapshot, polled by the viewer for a "real-time" feel.
     */
    public function progress(Request $request, Event $event): JsonResponse
    {
        $this->authorizeEvent($request->user(), $event);

        return response()->json(['data' => $this->progressFor($event)]);
    }

    /**
     * Events that belong to this organizer — either tied to their organization
     * or submitted by them through the planner.
     *
     * @return Builder<Event>
     */
    private function scopedEvents(User $user): Builder
    {
        return Event::query()->where(function (Builder $query) use ($user): void {
            if ($user->organization_id !== null) {
                $query->where('organization_id', $user->organization_id);
            }

            $query->orWhereHas('eventRequest', fn (Builder $r) => $r->where('submitted_by', $user->id));
        });
    }

    private function authorizeEvent(User $user, Event $event): void
    {
        $owns = $this->scopedEvents($user)->whereKey($event->getKey())->exists();

        if (! $owns) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * @param  Collection<int, Event>  $events
     * @return array<string, mixed>
     */
    private function stats(Collection $events): array
    {
        $now = Carbon::now();

        $totalSpent = 0.0;
        $totalMinutes = 0;
        $totalGuests = 0;
        $venues = [];
        $spendByType = [];
        $finished = 0;
        $upcoming = 0;
        $nextEvent = null;

        foreach ($events as $event) {
            $price = $this->priceOf($event);
            $totalSpent += $price;

            $minutes = $event->start_time && $event->end_time
                ? max(0, $event->start_time->diffInMinutes($event->end_time))
                : 0;
            $totalMinutes += $minutes;
            $totalGuests += (int) ($event->attendees ?? 0);

            $venueName = $event->eventRequest?->matchedSpace?->name;
            if ($venueName !== null) {
                $venues[$venueName] = ($venues[$venueName] ?? 0) + 1;
            }

            $type = $event->event_type?->label() ?? 'Other';
            $spendByType[$type] = ($spendByType[$type] ?? 0) + $price;

            if ($this->isFinished($event, $now)) {
                $finished++;
            } else {
                $upcoming++;
                if ($event->start_time !== null && ($nextEvent === null || $event->start_time->lt($nextEvent->start_time))) {
                    $nextEvent = $event;
                }
            }
        }

        $count = $events->count();
        arsort($venues);
        arsort($spendByType);

        return [
            'events_total' => $count,
            'upcoming_count' => $upcoming,
            'finished_count' => $finished,
            'total_spent' => round($totalSpent, 2),
            'total_hours' => round($totalMinutes / 60, 1),
            'total_guests' => $totalGuests,
            'venues_used' => count($venues),
            'favorite_venue' => array_key_first($venues),
            'avg_spend' => $count > 0 ? round($totalSpent / $count, 2) : 0,
            'avg_guests' => $count > 0 ? (int) round($totalGuests / $count) : 0,
            'since' => $events->min('start_time')?->toIso8601String(),
            'spend_by_type' => collect($spendByType)
                ->map(fn (float $amount, string $type): array => ['type' => $type, 'amount' => round($amount, 2)])
                ->values()
                ->all(),
            'next_event' => $nextEvent !== null ? [
                'id' => $nextEvent->id,
                'title' => $nextEvent->title,
                'start_time' => $nextEvent->start_time?->toIso8601String(),
                'venue' => $nextEvent->eventRequest?->matchedSpace?->name,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCard(Event $event): array
    {
        $now = Carbon::now();
        $total = (int) ($event->tasks_count ?? 0);
        $done = (int) ($event->finished_tasks_count ?? 0);

        return [
            'id' => $event->id,
            'title' => $event->title,
            'event_type_label' => $event->event_type?->label(),
            'status' => $event->status->value,
            'status_label' => $event->status->label(),
            'start_time' => $event->start_time?->toIso8601String(),
            'end_time' => $event->end_time?->toIso8601String(),
            'attendees' => $event->attendees,
            'venue' => $event->eventRequest?->matchedSpace?->name,
            'price' => $this->priceOf($event),
            'duration_hours' => $event->start_time && $event->end_time
                ? round($event->start_time->diffInMinutes($event->end_time) / 60, 1)
                : null,
            'is_finished' => $this->isFinished($event, $now),
            'tasks_total' => $total,
            'tasks_done' => $done,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeDetail(Event $event): array
    {
        $space = $event->eventRequest?->matchedSpace;

        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description ?? $event->eventRequest?->description,
            'event_type_label' => $event->event_type?->label(),
            'status' => $event->status->value,
            'status_label' => $event->status->label(),
            'start_time' => $event->start_time?->toIso8601String(),
            'end_time' => $event->end_time?->toIso8601String(),
            'attendees' => $event->attendees,
            'price' => $this->priceOf($event),
            'venue' => $space !== null ? [
                'name' => $space->name,
                'zone_class' => $space->zone_class,
                'floor' => $space->floor,
                'capacity' => $space->capacity,
                'box_ref' => $space->box_ref,
                'location_geometry' => $space->location_geometry,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function progressFor(Event $event): array
    {
        $tasks = $event->tasks()
            ->with('worker:id,name,worker_role')
            ->orderBy('phase')
            ->get();

        $byState = [];
        $weighted = 0.0;
        $counted = 0;

        foreach ($tasks as $task) {
            $state = $task->state->value;
            $byState[$state] = ($byState[$state] ?? 0) + 1;

            if ($state !== 'cancelled') {
                $weighted += self::READINESS_WEIGHTS[$state] ?? 0.0;
                $counted++;
            }
        }

        $readiness = $counted > 0 ? (int) round($weighted / $counted * 100) : 0;

        return [
            'readiness' => $readiness,
            'total' => $tasks->count(),
            'by_state' => $byState,
            'tasks' => $tasks->map(fn (Task $task): array => [
                'id' => $task->id,
                'name' => $task->name,
                'phase' => $task->phase->value,
                'phase_label' => $task->phase->label(),
                'state' => $task->state->value,
                'state_label' => $task->state->label(),
                'worker' => $task->worker?->only(['id', 'name', 'worker_role']),
            ])->all(),
        ];
    }

    private function priceOf(Event $event): float
    {
        $agreed = $event->eventRequest?->price_agreed;

        if ($agreed !== null) {
            return (float) $agreed;
        }

        return (float) ($event->budget ?? 0);
    }

    private function isFinished(Event $event, Carbon $now): bool
    {
        if (in_array($event->status->value, ['completed', 'cancelled'], true)) {
            return true;
        }

        return $event->end_time !== null && $event->end_time->lt($now);
    }
}
