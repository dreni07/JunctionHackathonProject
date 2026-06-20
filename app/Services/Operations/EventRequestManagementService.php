<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Enums\ActivityLogAction;
use App\Enums\EventRequestStatus;
use App\Enums\EventStatus;
use App\Enums\InvoiceStatus;
use App\Models\Event;
use App\Models\EventDecision;
use App\Models\EventRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\EventRequestAccepted;
use App\Notifications\EventRequestRejected;
use App\Services\ActivityLogService;
use App\Support\OperationalAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            EventRequest::query()->with(['submitter:id,name,email', 'matchedSpace:id,name,zone_class,capacity,box_ref,location_geometry']),
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
            'matchedSpace:id,name,zone_class,capacity,box_ref,location_geometry',
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

            // Book the agreed price as revenue for the worker's branch.
            $this->recordRevenue($user, $eventRequest, $event);

            // Save the accept decision for the agent to learn from.
            $this->recordDecision($user, $eventRequest, $event, 'accepted', null);

            // Let the organizer who submitted the request know it was accepted
            // (in-app notification + email).
            $eventRequest->submitter?->notify(new EventRequestAccepted($eventRequest, $event));

            return $event->load(['creator:id,name', 'eventRequest:id,title,status']);
        });
    }

    /**
     * Decline an event request, telling the organizer why, and recording the
     * reason for the agent's learning set.
     */
    public function reject(User $user, EventRequest $eventRequest, string $reason): EventRequest
    {
        if (! OperationalAccess::managesRequests($user)) {
            throw new InvalidArgumentException('You are not allowed to decline event requests.');
        }

        if ($eventRequest->event_id !== null || $eventRequest->status === EventRequestStatus::Converted) {
            throw new RuntimeException('This event request has already been accepted and cannot be declined.');
        }

        return DB::transaction(function () use ($user, $eventRequest, $reason): EventRequest {
            $eventRequest->update(['status' => EventRequestStatus::Rejected->value]);

            $this->activityLog->record(
                ActivityLogAction::Rejected,
                'Event request declined: '.$reason,
                $user,
                $eventRequest,
                properties: ['reason' => $reason],
            );

            $this->recordDecision($user, $eventRequest, null, 'rejected', $reason);

            $eventRequest->submitter?->notify(new EventRequestRejected($eventRequest, $reason));

            return $eventRequest->fresh(['submitter:id,name,email', 'matchedSpace:id,name']);
        });
    }

    /**
     * Record the agreed price as collected revenue: a paid invoice plus a
     * payment, so it shows up in the branch's money system.
     */
    private function recordRevenue(User $user, EventRequest $eventRequest, Event $event): void
    {
        $amount = $eventRequest->price_agreed !== null ? (float) $eventRequest->price_agreed : 0.0;

        if ($user->tenant_id === null || $amount <= 0) {
            return;
        }

        $invoice = Invoice::query()->create([
            'tenant_id' => $user->tenant_id,
            'event_id' => $event->id,
            'organization_id' => $eventRequest->organization_id,
            'reference' => 'EVT-'.Str::upper(Str::substr(str_replace('-', '', $event->id), 0, 10)),
            'title' => 'Booking — '.($eventRequest->title ?? 'Event'),
            'amount' => round($amount, 2),
            'amount_paid' => round($amount, 2),
            'status' => InvoiceStatus::Paid->value,
            'issued_at' => now(),
        ]);

        Payment::query()->create([
            'invoice_id' => $invoice->id,
            'amount' => round($amount, 2),
            'method' => 'other',
            'paid_at' => now(),
            'recorded_by' => $user->id,
            'notes' => 'Confirmed event booking.',
        ]);
    }

    /**
     * Persist an accept/reject decision into the agent's learning set.
     */
    private function recordDecision(User $user, EventRequest $eventRequest, ?Event $event, string $decision, ?string $reason): void
    {
        EventDecision::query()->create([
            'event_request_id' => $eventRequest->id,
            'event_id' => $event?->id,
            'decided_by' => $user->id,
            'matched_space_id' => $eventRequest->matched_space_id,
            'decision' => $decision,
            'rejection_reason' => $reason,
            'event_type' => $eventRequest->event_type?->value,
            'attendees' => $eventRequest->attendees,
            'price_suggested' => $eventRequest->price_suggested,
            'price_agreed' => $eventRequest->price_agreed,
            'features' => [
                'zone_class' => $eventRequest->relationLoaded('matchedSpace') ? $eventRequest->matchedSpace?->zone_class : null,
                'price_per_sqm' => $eventRequest->price_per_sqm,
            ],
        ]);
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
            'matched_space' => $eventRequest->relationLoaded('matchedSpace') ? $eventRequest->matchedSpace?->only(['id', 'name', 'zone_class', 'capacity', 'box_ref', 'location_geometry']) : null,
            'event_id' => $eventRequest->event_id,
            'final_proposal_id' => $eventRequest->final_proposal_id,
            'created_at' => $eventRequest->created_at?->toIso8601String(),
            'updated_at' => $eventRequest->updated_at?->toIso8601String(),
        ];
    }
}
