<?php

use App\Enums\ActivityLogAction;
use App\Enums\AlertSource;
use App\Enums\AlertStatus;
use App\Enums\ApprovalDecision;
use App\Enums\ConflictType;
use App\Enums\EventRequestStatus;
use App\Enums\QuotationLineCategory;
use App\Enums\SpaceType;
use App\Enums\TaskPhase;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Approval;
use App\Models\Conflict;
use App\Models\Event;
use App\Models\EventContact;
use App\Models\EventRequest;
use App\Models\FinalProposal;
use App\Models\QuotationLineItem;
use App\Models\Space;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

test('event domain tables exist after migration', function () {
    expect(Schema::hasTable('event_requests'))->toBeTrue()
        ->and(Schema::hasTable('quotation_line_items'))->toBeTrue()
        ->and(Schema::hasTable('conflicts'))->toBeTrue()
        ->and(Schema::hasTable('activity_logs'))->toBeTrue()
        ->and(Schema::hasTable('approvals'))->toBeTrue()
        ->and(Schema::hasTable('event_contacts'))->toBeTrue()
        ->and(Schema::hasTable('alerts'))->toBeTrue();
});

test('event request can link through proposal to event with supporting records', function () {
    $submitter = User::factory()->create();
    $space = Space::query()->create([
        'name' => 'Orange Hall',
        'floor' => 0,
        'capacity' => 200,
        'type' => SpaceType::Hall,
    ]);

    $request = EventRequest::factory()->create([
        'submitted_by' => $submitter->id,
        'status' => EventRequestStatus::UnderReview,
    ]);

    EventContact::query()->create([
        'event_request_id' => $request->id,
        'name' => $submitter->name,
        'email' => $submitter->email,
        'is_primary' => true,
    ]);

    $proposal = FinalProposal::query()->create([
        'organization_id' => $request->organization_id,
        'event_request_id' => $request->id,
        'created_by' => $submitter->id,
        'proposed_space_id' => $space->id,
        'proposed_capacity' => 180,
        'proposed_price' => 1500,
        'title' => $request->title,
        'start_at' => $request->preferred_start_at,
        'end_at' => $request->preferred_end_at,
    ]);

    QuotationLineItem::query()->create([
        'final_proposal_id' => $proposal->id,
        'description' => 'Orange Hall rental (4 hours)',
        'category' => QuotationLineCategory::Space,
        'quantity' => 1,
        'unit_price' => 1200,
        'total' => 1200,
        'sort_order' => 1,
    ]);

    Approval::query()->create([
        'approvable_type' => FinalProposal::class,
        'approvable_id' => $proposal->id,
        'requested_by' => $submitter->id,
        'decision' => ApprovalDecision::Pending,
    ]);

    Conflict::query()->create([
        'event_request_id' => $request->id,
        'final_proposal_id' => $proposal->id,
        'space_id' => $space->id,
        'type' => ConflictType::SpaceOverlap,
        'title' => 'Orange Hall overlap detected',
        'detected_at' => now(),
    ]);

    ActivityLog::query()->create([
        'event_request_id' => $request->id,
        'final_proposal_id' => $proposal->id,
        'user_id' => $submitter->id,
        'action' => ActivityLogAction::Created,
        'description' => 'Event request submitted',
    ]);

    Alert::query()->create([
        'event_request_id' => $request->id,
        'final_proposal_id' => $proposal->id,
        'source' => AlertSource::Agent,
        'status' => AlertStatus::Unread,
        'title' => 'Asset shortage predicted',
        'message' => 'Only 120 chairs available for 180 attendees in Orange Hall.',
        'agent_name' => 'FeasibilityAgent',
    ]);

    $event = Event::query()->create([
        'title' => $proposal->title,
        'organization_id' => $proposal->organization_id,
        'event_request_id' => $request->id,
        'created_by' => $submitter->id,
    ]);

    Task::query()->create([
        'event_id' => $event->id,
        'name' => 'Arrange chairs in theater layout',
        'phase' => TaskPhase::Setup,
        'due_at' => now()->addDay(),
    ]);

    $request->update([
        'final_proposal_id' => $proposal->id,
        'event_id' => $event->id,
        'status' => EventRequestStatus::Converted,
    ]);

    $request->refresh()->load(['contacts', 'finalProposal.lineItems', 'conflicts', 'activityLogs', 'alerts', 'event.tasks']);

    expect($request->status)->toBe(EventRequestStatus::Converted)
        ->and($request->contacts)->toHaveCount(1)
        ->and($request->finalProposal?->lineItems)->toHaveCount(1)
        ->and($request->conflicts)->toHaveCount(1)
        ->and($request->activityLogs)->toHaveCount(1)
        ->and($request->alerts)->toHaveCount(1)
        ->and($request->event?->tasks)->toHaveCount(1)
        ->and($request->event?->tasks->first()?->phase)->toBe(TaskPhase::Setup);
});
