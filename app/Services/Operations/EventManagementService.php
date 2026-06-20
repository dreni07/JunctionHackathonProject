<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Enums\ActivityLogAction;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Support\OperationalAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class EventManagementService
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    /**
     * @param  array{status?: string, search?: string}  $filters
     * @return LengthAwarePaginator<int, Event>
     */
    public function paginate(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = OperationalAccess::scopeEvents(
            Event::query()->with(['creator:id,name', 'organization:id,name']),
            $user,
        )->latest('start_time');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where('title', 'like', $term);
        }

        return $query->paginate($perPage);
    }

    public function find(User $user, Event $event): Event
    {
        OperationalAccess::ensureCanViewEvent($user, $event);

        return $event->load([
            'creator:id,name,email',
            'organization:id,name',
            'eventRequest:id,title,status',
            'reservations.space:id,name,zone_class',
            'tasks.worker:id,name',
            'contacts',
            'conflicts',
            'activityLogs.user:id,name',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, Event $event, array $data): Event
    {
        if (! OperationalAccess::managesEvents($user)) {
            throw new InvalidArgumentException('You are not allowed to update events.');
        }

        $payload = [];

        if (array_key_exists('title', $data)) {
            $payload['title'] = $data['title'];
        }

        if (array_key_exists('description', $data)) {
            $payload['description'] = $data['description'];
        }

        if (array_key_exists('start_time', $data)) {
            $payload['start_time'] = $data['start_time'];
        }

        if (array_key_exists('end_time', $data)) {
            $payload['end_time'] = $data['end_time'];
        }

        if (array_key_exists('budget', $data)) {
            $payload['budget'] = $data['budget'];
        }

        if (array_key_exists('attendees', $data)) {
            $payload['attendees'] = $data['attendees'];
        }

        if (array_key_exists('event_type', $data)) {
            $type = EventType::tryFrom((string) $data['event_type']);
            if (! $type instanceof EventType) {
                throw new InvalidArgumentException('Invalid event type.');
            }
            $payload['event_type'] = $type->value;
        }

        if (array_key_exists('status', $data)) {
            $status = EventStatus::tryFrom((string) $data['status']);
            if (! $status instanceof EventStatus) {
                throw new InvalidArgumentException('Invalid event status.');
            }
            $payload['status'] = $status->value;
        }

        $event->update($payload);

        $this->activityLog->record(
            ActivityLogAction::Updated,
            'Event details updated',
            $user,
            event: $event,
        );

        return $event->fresh(['creator:id,name', 'organization:id,name']);
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Event $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'status' => $event->status->value,
            'status_label' => $event->status->label(),
            'event_type' => $event->event_type?->value,
            'event_type_label' => $event->event_type?->label(),
            'attendees' => $event->attendees,
            'start_time' => $event->start_time?->toIso8601String(),
            'end_time' => $event->end_time?->toIso8601String(),
            'budget' => $event->budget,
            'organization_id' => $event->organization_id,
            'event_request_id' => $event->event_request_id,
            'created_by' => $event->created_by,
            'creator' => $event->relationLoaded('creator') ? $event->creator?->only(['id', 'name', 'email']) : null,
            'created_at' => $event->created_at?->toIso8601String(),
            'updated_at' => $event->updated_at?->toIso8601String(),
        ];
    }
}
