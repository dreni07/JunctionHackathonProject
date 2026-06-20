<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Authorization\Permissions;
use App\Enums\ActivityLogAction;
use App\Enums\ApprovalDecision;
use App\Enums\EventRequestStatus;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\ProposalStatus;
use App\Enums\QuotationLineCategory;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\FinalProposal;
use App\Models\QuotationLineItem;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Support\OperationalAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class QuotationManagementService
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    /**
     * @return LengthAwarePaginator<int, FinalProposal>
     */
    public function paginate(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = OperationalAccess::scopeProposals(
            FinalProposal::query()->with(['creator:id,name', 'proposedSpace:id,name']),
            $user,
        )->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['event_request_id'])) {
            $query->where('event_request_id', $filters['event_request_id']);
        }

        return $query->paginate($perPage);
    }

    public function find(User $user, FinalProposal $proposal): FinalProposal
    {
        OperationalAccess::ensureCanViewProposal($user, $proposal);

        return $proposal->load([
            'creator:id,name',
            'proposedSpace:id,name,zone_class,capacity',
            'eventRequest:id,title,status',
            'lineItems',
            'approvals.requester:id,name',
            'approvals.decider:id,name',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): FinalProposal
    {
        if (! $user->hasPermissionTo(Permissions::QUOTATIONS_CREATE)) {
            throw new InvalidArgumentException('You are not allowed to create proposals.');
        }

        $eventRequest = EventRequest::query()->findOrFail($data['event_request_id']);
        OperationalAccess::ensureCanViewEventRequest($user, $eventRequest);

        $eventType = isset($data['event_type'])
            ? EventType::tryFrom((string) $data['event_type'])
            : $eventRequest->event_type;

        $proposal = FinalProposal::query()->create([
            'organization_id' => $eventRequest->organization_id,
            'event_request_id' => $eventRequest->id,
            'created_by' => $user->id,
            'proposed_space_id' => $data['proposed_space_id'] ?? $eventRequest->matched_space_id,
            'proposed_capacity' => $data['proposed_capacity'] ?? $eventRequest->attendees,
            'proposed_price' => $data['proposed_price'] ?? 0,
            'title' => $data['title'] ?? $eventRequest->title,
            'event_type' => $eventType?->value,
            'start_at' => $data['start_at'] ?? $eventRequest->preferred_start_at,
            'end_at' => $data['end_at'] ?? $eventRequest->preferred_end_at,
            'description' => $data['description'] ?? $eventRequest->description,
            'status' => ProposalStatus::Draft->value,
        ]);

        $eventRequest->update([
            'final_proposal_id' => $proposal->id,
            'status' => EventRequestStatus::ProposalDraft->value,
        ]);

        $this->activityLog->record(
            ActivityLogAction::Created,
            'Quotation draft created',
            $user,
            $eventRequest,
            proposal: $proposal,
        );

        return $proposal->load(['lineItems', 'proposedSpace:id,name']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, FinalProposal $proposal, array $data): FinalProposal
    {
        if (! $user->hasPermissionTo(Permissions::QUOTATIONS_CREATE)) {
            throw new InvalidArgumentException('You are not allowed to update proposals.');
        }

        OperationalAccess::ensureCanViewProposal($user, $proposal);

        if ($proposal->status !== ProposalStatus::Draft) {
            throw new RuntimeException('Only draft proposals can be edited.');
        }

        $payload = collect($data)->only([
            'title', 'description', 'proposed_space_id', 'proposed_capacity',
            'proposed_price', 'start_at', 'end_at',
        ])->filter(fn ($v) => $v !== null)->all();

        if (isset($data['event_type'])) {
            $type = EventType::tryFrom((string) $data['event_type']);
            if ($type instanceof EventType) {
                $payload['event_type'] = $type->value;
            }
        }

        $proposal->update($payload);

        return $proposal->fresh(['lineItems', 'proposedSpace:id,name']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addLineItem(User $user, FinalProposal $proposal, array $data): QuotationLineItem
    {
        if (! $user->hasPermissionTo(Permissions::QUOTATIONS_CREATE)) {
            throw new InvalidArgumentException('You are not allowed to edit proposals.');
        }

        if ($proposal->status !== ProposalStatus::Draft) {
            throw new RuntimeException('Line items can only be added to draft proposals.');
        }

        $category = QuotationLineCategory::tryFrom((string) $data['category']);
        if (! $category instanceof QuotationLineCategory) {
            throw new InvalidArgumentException('Invalid line item category.');
        }

        $quantity = (int) $data['quantity'];
        $unitPrice = (string) $data['unit_price'];
        $total = bcmul((string) $quantity, $unitPrice, 2);

        $lineItem = QuotationLineItem::query()->create([
            'final_proposal_id' => $proposal->id,
            'description' => $data['description'],
            'category' => $category->value,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $total,
            'sort_order' => (int) ($data['sort_order'] ?? ($proposal->lineItems()->max('sort_order') + 1)),
        ]);

        $this->recalculateProposalTotal($proposal);

        return $lineItem;
    }

    public function removeLineItem(User $user, FinalProposal $proposal, QuotationLineItem $lineItem): void
    {
        if (! $user->hasPermissionTo(Permissions::QUOTATIONS_CREATE)) {
            throw new InvalidArgumentException('You are not allowed to edit proposals.');
        }

        if ($lineItem->final_proposal_id !== $proposal->id) {
            throw new InvalidArgumentException('Line item does not belong to this proposal.');
        }

        $lineItem->delete();
        $this->recalculateProposalTotal($proposal);
    }

    public function submit(User $user, FinalProposal $proposal): FinalProposal
    {
        if (! $user->hasPermissionTo(Permissions::QUOTATIONS_CREATE)) {
            throw new InvalidArgumentException('You are not allowed to submit proposals.');
        }

        if ($proposal->status !== ProposalStatus::Draft) {
            throw new RuntimeException('Only draft proposals can be submitted.');
        }

        $proposal->update(['status' => ProposalStatus::Sent->value]);

        $proposal->approvals()->create([
            'requested_by' => $user->id,
            'decision' => ApprovalDecision::Pending->value,
        ]);

        $this->activityLog->record(
            ActivityLogAction::ProposalSent,
            'Proposal submitted for approval',
            $user,
            $proposal->eventRequest,
            proposal: $proposal,
        );

        return $proposal->fresh(['approvals']);
    }

    public function approve(User $user, FinalProposal $proposal, ?string $notes = null): FinalProposal
    {
        if (! $user->hasPermissionTo(Permissions::QUOTATIONS_APPROVE)) {
            throw new InvalidArgumentException('You are not allowed to approve proposals.');
        }

        return DB::transaction(function () use ($user, $proposal, $notes): FinalProposal {
            $approval = $proposal->approvals()->where('decision', ApprovalDecision::Pending->value)->latest()->first();

            if ($approval !== null) {
                $approval->update([
                    'decision' => ApprovalDecision::Approved->value,
                    'decided_by' => $user->id,
                    'decided_at' => now(),
                    'notes' => $notes,
                ]);
            }

            $proposal->update(['status' => ProposalStatus::Accepted->value]);

            $event = $proposal->event;

            if (! $event instanceof Event) {
                $event = Event::query()->create([
                    'title' => $proposal->title,
                    'description' => $proposal->description,
                    'status' => EventStatus::Approved->value,
                    'event_type' => $proposal->event_type?->value,
                    'attendees' => $proposal->proposed_capacity,
                    'start_time' => $proposal->start_at,
                    'end_time' => $proposal->end_at,
                    'budget' => $proposal->proposed_price,
                    'organization_id' => $proposal->organization_id,
                    'event_request_id' => $proposal->event_request_id,
                    'created_by' => $user->id,
                ]);

                $proposal->update(['event_id' => $event->id]);

                if ($proposal->event_request_id !== null) {
                    EventRequest::query()->whereKey($proposal->event_request_id)->update([
                        'event_id' => $event->id,
                        'status' => EventRequestStatus::Converted->value,
                    ]);
                }
            }

            $this->activityLog->record(
                ActivityLogAction::Approved,
                'Proposal approved',
                $user,
                $proposal->eventRequest,
                $event,
                $proposal,
                ['notes' => $notes],
            );

            return $proposal->fresh(['event:id,title,status', 'approvals']);
        });
    }

    public function reject(User $user, FinalProposal $proposal, ?string $notes = null): FinalProposal
    {
        if (! $user->hasPermissionTo(Permissions::QUOTATIONS_APPROVE)) {
            throw new InvalidArgumentException('You are not allowed to reject proposals.');
        }

        $approval = $proposal->approvals()->where('decision', ApprovalDecision::Pending->value)->latest()->first();

        if ($approval !== null) {
            $approval->update([
                'decision' => ApprovalDecision::Rejected->value,
                'decided_by' => $user->id,
                'decided_at' => now(),
                'notes' => $notes,
            ]);
        }

        $proposal->update(['status' => ProposalStatus::Rejected->value]);

        $this->activityLog->record(
            ActivityLogAction::Rejected,
            'Proposal rejected',
            $user,
            $proposal->eventRequest,
            proposal: $proposal,
            properties: ['notes' => $notes],
        );

        return $proposal->fresh(['approvals']);
    }

    private function recalculateProposalTotal(FinalProposal $proposal): void
    {
        $total = $proposal->lineItems()->sum('total');
        $proposal->update(['proposed_price' => $total]);
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(FinalProposal $proposal): array
    {
        return [
            'id' => $proposal->id,
            'title' => $proposal->title,
            'description' => $proposal->description,
            'status' => $proposal->status->value,
            'status_label' => $proposal->status->label(),
            'event_type' => $proposal->event_type?->value,
            'proposed_space_id' => $proposal->proposed_space_id,
            'proposed_capacity' => $proposal->proposed_capacity,
            'proposed_price' => $proposal->proposed_price,
            'start_at' => $proposal->start_at?->toIso8601String(),
            'end_at' => $proposal->end_at?->toIso8601String(),
            'event_request_id' => $proposal->event_request_id,
            'event_id' => $proposal->event_id,
            'line_items' => $proposal->relationLoaded('lineItems')
                ? $proposal->lineItems->map(fn (QuotationLineItem $item): array => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'category' => $item->category->value,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                    'sort_order' => $item->sort_order,
                ])->all()
                : [],
        ];
    }
}
