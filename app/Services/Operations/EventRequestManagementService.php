<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Enums\ActivityLogAction;
use App\Enums\EventRequestStatus;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Support\OperationalAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class EventRequestManagementService
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    /**
     * @param  array{status?: string, search?: string}  $filters
     * @return LengthAwarePaginator<int, EventRequest>
     */
    public function paginate(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = OperationalAccess::scopeEventRequests(
            EventRequest::query()->with(['submitter:id,name,email', 'matchedSpace:id,name,zone_class']),
            $user,
        )->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($term): void {
                $q->where('title', 'like', $term)->orWhere('description', 'like', $term);
            });
        }

        return $query->paginate($perPage);
    }

    public function find(User $user, EventRequest $eventRequest): EventRequest
    {
        OperationalAccess::ensureCanViewEventRequest($user, $eventRequest);

        return $eventRequest->load([
            'submitter:id,name,email',
            'matchedSpace:id,name,zone_class,capacity',
            'venueMatches.space:id,name,zone_class',
            'event:id,title,status',
            'finalProposal:id,title,status',
            'contacts',
            'conflicts',
            'activityLogs.user:id,name',
        ]);
    }

    /**
     * @param  array{status: string, notes?: string|null}  $data
     */
    public function updateStatus(User $user, EventRequest $eventRequest, array $data): EventRequest
    {
        if (! OperationalAccess::managesRequests($user)) {
            throw new InvalidArgumentException('You are not allowed to update event request status.');
        }

        $status = EventRequestStatus::tryFrom($data['status']);

        if (! $status instanceof EventRequestStatus) {
            throw new InvalidArgumentException('Invalid event request status.');
        }

        if ($eventRequest->status === EventRequestStatus::Converted && $status !== EventRequestStatus::Converted) {
            throw new InvalidArgumentException('Converted requests cannot change status.');
        }

        $previous = $eventRequest->status;
        $eventRequest->update(['status' => $status->value]);

        $this->activityLog->record(
            ActivityLogAction::StatusChanged,
            "Event request status changed from {$previous->label()} to {$status->label()}",
            $user,
            $eventRequest,
            properties: ['from' => $previous->value, 'to' => $status->value, 'notes' => $data['notes'] ?? null],
        );

        return $eventRequest->fresh(['submitter:id,name,email', 'matchedSpace:id,name']);
    }

    public function convertToEvent(User $user, EventRequest $eventRequest): Event
    {
        if (! OperationalAccess::managesRequests($user)) {
            throw new InvalidArgumentException('You are not allowed to convert event requests.');
        }

        if ($eventRequest->event_id !== null) {
            throw new RuntimeException('This event request has already been converted.');
        }

        return DB::transaction(function () use ($user, $eventRequest): Event {
            $event = Event::query()->create([
                'title' => $eventRequest->title,
                'description' => $eventRequest->description,
                'status' => EventStatus::Planning->value,
                'event_type' => $eventRequest->event_type?->value,
                'attendees' => $eventRequest->attendees,
                'start_time' => $eventRequest->preferred_start_at,
                'end_time' => $eventRequest->preferred_end_at,
                'organization_id' => $eventRequest->organization_id,
                'event_request_id' => $eventRequest->id,
                'created_by' => $user->id,
            ]);

            $eventRequest->update([
                'event_id' => $event->id,
                'status' => EventRequestStatus::Converted->value,
            ]);

            $this->activityLog->record(
                ActivityLogAction::Converted,
                'Event request converted to operational event',
                $user,
                $eventRequest,
                $event,
            );

            return $event->load(['creator:id,name', 'eventRequest:id,title,status']);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(EventRequest $eventRequest): array
    {
        return [
            'id' => $eventRequest->id,
            'title' => $eventRequest->title,
            'description' => $eventRequest->description,
            'event_type' => $eventRequest->event_type?->value,
            'event_type_label' => $eventRequest->event_type?->label(),
            'attendees' => $eventRequest->attendees,
            'price_suggested' => $eventRequest->price_suggested,
            'price_agreed' => $eventRequest->price_agreed,
            'price_per_sqm' => $eventRequest->price_per_sqm,
            'preferred_start_at' => $eventRequest->preferred_start_at?->toIso8601String(),
            'preferred_end_at' => $eventRequest->preferred_end_at?->toIso8601String(),
            'status' => $eventRequest->status->value,
            'status_label' => $eventRequest->status->label(),
            'organization_id' => $eventRequest->organization_id,
            'submitted_by' => $eventRequest->submitted_by,
            'submitter' => $eventRequest->relationLoaded('submitter') ? $eventRequest->submitter?->only(['id', 'name', 'email']) : null,
            'matched_space_id' => $eventRequest->matched_space_id,
            'matched_space' => $eventRequest->relationLoaded('matchedSpace') ? $eventRequest->matchedSpace?->only(['id', 'name', 'zone_class', 'capacity']) : null,
            'event_id' => $eventRequest->event_id,
            'final_proposal_id' => $eventRequest->final_proposal_id,
            'created_at' => $eventRequest->created_at?->toIso8601String(),
            'updated_at' => $eventRequest->updated_at?->toIso8601String(),
        ];
    }
}
