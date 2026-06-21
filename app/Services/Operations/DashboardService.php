<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Authorization\Permissions;
use App\Enums\AlertStatus;
use App\Enums\ConflictStatus;
use App\Enums\EventRequestStatus;
use App\Enums\TaskState;
use App\Models\Alert;
use App\Models\Conflict;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\Task;
use App\Models\User;
use App\Support\OperationalAccess;
use Illuminate\Support\Carbon;

class DashboardService
{
    /**
     * @return array<string, int>
     */
    public function summary(User $user): array
    {
        $requests = OperationalAccess::scopeEventRequests(EventRequest::query(), $user);
        $events = OperationalAccess::scopeEvents(Event::query(), $user);

        return [
            'pending_requests' => (clone $requests)
                ->whereIn('status', [
                    EventRequestStatus::Submitted->value,
                    EventRequestStatus::UnderReview->value,
                    EventRequestStatus::ProposalDraft->value,
                ])
                ->count(),
            'events_this_week' => (clone $events)
                ->whereBetween('start_time', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count(),
            'open_conflicts' => Conflict::query()
                ->when(! OperationalAccess::managesRequests($user), fn ($q) => $q->whereHas(
                    'eventRequest',
                    fn ($rq) => OperationalAccess::scopeEventRequests($rq, $user),
                ))
                ->whereIn('status', [ConflictStatus::Open->value, ConflictStatus::Acknowledged->value])
                ->count(),
            'tasks_due_today' => Task::query()
                ->when(! OperationalAccess::managesEvents($user), fn ($q) => $q->whereHas(
                    'event',
                    fn ($ev) => OperationalAccess::scopeEvents($ev, $user),
                ))
                ->whereDate('due_at', Carbon::today())
                ->whereNotIn('state', [TaskState::Finished->value, TaskState::Cancelled->value])
                ->count(),
            'unread_alerts' => Alert::query()
                ->where('status', AlertStatus::Unread->value)
                ->when(! $user->hasPermissionTo(Permissions::REQUESTS_MANAGE), function ($q) use ($user): void {
                    $q->where(function ($scoped) use ($user): void {
                        $scoped->where('user_id', $user->id)->orWhereNull('user_id');
                    });
                })
                ->count(),
        ];
    }
}
