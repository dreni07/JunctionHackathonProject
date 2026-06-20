<?php

declare(strict_types=1);

use App\Enums\EventRequestStatus;
use App\Enums\EventStatus;
use App\Enums\RoleName;
use App\Enums\SpaceType;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\Organization;
use App\Models\Space;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function operationsUser(): User
{
    $user = User::factory()->create();
    $user->assignRole(RoleName::Operations);

    return $user;
}

function organizerUser(): User
{
    $user = User::factory()->create();
    $user->assignRole(RoleName::Organizer);

    return $user;
}

test('guests cannot access operations endpoints', function (): void {
    $this->getJson(route('operations.dashboard'))->assertRedirect(route('login'));
});

test('operations user can load dashboard summary', function (): void {
    $this->actingAs(operationsUser())
        ->getJson(route('operations.dashboard'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'pending_requests',
                'events_this_week',
                'open_conflicts',
                'tasks_due_today',
                'unread_alerts',
            ],
        ]);
});

test('operations user can list and update event requests', function (): void {
    $user = operationsUser();
    $request = EventRequest::factory()->create(['status' => EventRequestStatus::Submitted]);

    $this->actingAs($user)
        ->getJson(route('operations.event-requests.index'))
        ->assertOk()
        ->assertJsonPath('meta.total', 1);

    $this->actingAs($user)
        ->patchJson(route('operations.event-requests.update', $request), [
            'status' => EventRequestStatus::UnderReview->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.status', EventRequestStatus::UnderReview->value);
});

test('operations user can convert an event request into an event', function (): void {
    $user = operationsUser();
    $request = EventRequest::factory()->create(['status' => EventRequestStatus::UnderReview]);

    $this->actingAs($user)
        ->postJson(route('operations.event-requests.convert', $request))
        ->assertCreated()
        ->assertJsonPath('data.status', EventStatus::Planning->value);

    expect($request->fresh()->status)->toBe(EventRequestStatus::Converted)
        ->and($request->fresh()->event_id)->not->toBeNull();
});

test('organizer only sees their own event requests', function (): void {
    $organizer = organizerUser();
    $other = User::factory()->create();

    EventRequest::factory()->create(['submitted_by' => $organizer->id]);
    EventRequest::factory()->create(['submitted_by' => $other->id]);

    $this->actingAs($organizer)
        ->getJson(route('operations.event-requests.index'))
        ->assertOk()
        ->assertJsonPath('meta.total', 1);
});

test('operations user can manage events tasks and spaces', function (): void {
    $user = operationsUser();
    $event = Event::query()->create([
        'title' => 'Ops Summit',
        'status' => EventStatus::Planning->value,
        'created_by' => $user->id,
    ]);

    $space = Space::query()->create([
        'name' => 'Blue Studio',
        'floor' => 1,
        'capacity' => 80,
        'type' => SpaceType::Hall,
    ]);

    $this->actingAs($user)
        ->getJson(route('operations.events.index'))
        ->assertOk();

    $this->actingAs($user)
        ->patchJson(route('operations.events.update', $event), [
            'title' => 'Updated Summit',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Summit');

    $this->actingAs($user)
        ->postJson(route('operations.tasks.store'), [
            'event_id' => $event->id,
            'name' => 'Set up chairs',
            'phase' => 'setup',
        ])
        ->assertCreated();

    $this->actingAs($user)
        ->postJson(route('operations.events.reservations.store', $event), [
            'space_id' => $space->id,
            'start_at' => now()->addDays(3)->toIso8601String(),
            'end_at' => now()->addDays(3)->addHours(4)->toIso8601String(),
        ])
        ->assertCreated();

    $this->actingAs($user)
        ->getJson(route('operations.spaces.index'))
        ->assertOk()
        ->assertJsonPath('meta.total', 1);
});

test('management user can approve a sent proposal', function (): void {
    $management = User::factory()->create();
    $management->assignRole(RoleName::Management);

    $operations = operationsUser();
    $request = EventRequest::factory()->create(['status' => EventRequestStatus::ProposalDraft]);

    $proposalResponse = $this->actingAs($operations)
        ->postJson(route('operations.proposals.store'), [
            'event_request_id' => $request->id,
            'proposed_price' => 500,
        ])
        ->assertCreated();

    $proposalId = $proposalResponse->json('data.id');

    $this->actingAs($operations)
        ->postJson(route('operations.proposals.submit', $proposalId))
        ->assertOk();

    $this->actingAs($management)
        ->postJson(route('operations.proposals.approve', $proposalId), [
            'notes' => 'Looks good',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'accepted');
});

test('organization profile can be viewed by its member', function (): void {
    $organization = Organization::query()->create(['name' => 'Acme Events']);
    $user = organizerUser();
    $user->update(['organization_id' => $organization->id]);

    $this->actingAs($user)
        ->getJson(route('operations.organizations.show', $organization))
        ->assertOk()
        ->assertJsonPath('data.name', 'Acme Events');
});
