<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\EventRequestStatus;
use App\Enums\EventStatus;
use App\Enums\OrganizationType;
use App\Enums\TaskPhase;
use App\Enums\TaskState;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\Organization;
use App\Models\Space;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * A demo organizer with a portfolio of booked events — finished and upcoming —
 * so the "My events" viewer and its analytics have something rich to show.
 * Idempotent: keyed on the demo organizer's email.
 */
class OrganizationPortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->firstOrCreate(
            ['name' => 'Northstar Events'],
            ['type' => OrganizationType::Company->value],
        );

        $organizer = User::query()->firstOrCreate(
            ['email' => 'demo-organizer@pyramid.test'],
            [
                'name' => 'Nora Demo',
                'password' => Hash::make('password'),
                'account_type' => AccountType::Organization->value,
                'organization_id' => $organization->id,
                'email_verified_at' => now(),
            ],
        );

        if ($organizer->wasRecentlyCreated === false && $organizer->events()->exists()) {
            // Already seeded for this organizer.
            return;
        }

        $venues = Space::query()
            ->whereNotNull('room_code')
            ->where('zone_class', '!=', 'TUMO')
            ->orderByDesc('capacity')
            ->get();

        if ($venues->isEmpty()) {
            return;
        }

        // [title, type, attendees, daysFromNow, startHour, durationHours, price, status]
        $blueprint = [
            ['Annual Developers Summit', 'conference', 180, -120, 9, 8, 9800, EventStatus::Completed],
            ['Product Launch Night', 'private_event', 90, -64, 18, 4, 5200, EventStatus::Completed],
            ['Design Systems Workshop', 'workshop', 40, -30, 10, 6, 2600, EventStatus::Completed],
            ['Startup Demo Day', 'meetup', 110, 9, 17, 4, 4100, EventStatus::Active],
            ['Winter Tech Conference', 'conference', 160, 34, 9, 8, 8700, EventStatus::Planning],
        ];

        foreach ($blueprint as $i => [$title, $type, $attendees, $offset, $hour, $duration, $price, $status]) {
            $venue = $venues[$i % $venues->count()];
            $start = now()->addDays($offset)->setTime($hour, 0);
            $end = $start->copy()->addHours($duration);

            // Give each demo venue a spot on the floor plan (floor 1) so the
            // event viewer shows the highlighted map once a plan image exists.
            if ($venue->location_geometry === null) {
                $venue->update(['location_geometry' => [
                    'x' => round(0.28 + 0.22 * ($i % 3), 3),
                    'y' => round(0.32 + 0.13 * intdiv($i, 3), 3),
                    'level' => 1,
                ]]);
            }

            $request = EventRequest::query()->create([
                'organization_id' => $organization->id,
                'submitted_by' => $organizer->id,
                'title' => $title,
                'description' => $title.' hosted at the Pyramid of Tirana.',
                'event_type' => $type,
                'attendees' => $attendees,
                'preferred_start_at' => $start,
                'preferred_end_at' => $end,
                'status' => EventRequestStatus::Converted->value,
                'matched_space_id' => $venue->id,
                'price_suggested' => $price,
                'price_agreed' => $price,
                'price_per_sqm' => $venue->area_sqm > 0 ? round($price / $venue->area_sqm, 2) : null,
            ]);

            $event = Event::query()->create([
                'title' => $title,
                'description' => $request->description,
                'status' => $status->value,
                'event_type' => $type,
                'attendees' => $attendees,
                'start_time' => $start,
                'end_time' => $end,
                'organization_id' => $organization->id,
                'event_request_id' => $request->id,
                'created_by' => $organizer->id,
            ]);

            $request->update(['event_id' => $event->id]);

            $this->seedTasks($event, $status);
        }

        $this->command?->info('Seeded demo organizer (demo-organizer@pyramid.test) with '.count($blueprint).' events.');
    }

    /**
     * Give each event a small set of tasks. Finished events are fully done;
     * upcoming ones are part-way, so the readiness bar shows real progress.
     */
    private function seedTasks(Event $event, EventStatus $status): void
    {
        $worker = User::query()->where('account_type', AccountType::Operational->value)->inRandomOrder()->first();

        // [name, phase, stateForUpcoming]
        $plan = [
            ['Confirm room setup and seating', TaskPhase::Setup, TaskState::Finished],
            ['Set up AV and microphones', TaskPhase::Setup, TaskState::OnProcess],
            ['Brief the front-desk team', TaskPhase::Setup, TaskState::Ongoing],
            ['Coordinate catering delivery', TaskPhase::Setup, TaskState::Started],
            ['Run the registration desk', TaskPhase::During, TaskState::Pending],
            ['Pack down and store equipment', TaskPhase::Teardown, TaskState::Pending],
        ];

        $done = in_array($status, [EventStatus::Completed, EventStatus::Cancelled], true);

        foreach ($plan as [$name, $phase, $upcomingState]) {
            Task::query()->create([
                'event_id' => $event->id,
                'organization_id' => $event->organization_id,
                'user_id' => $worker?->id,
                'name' => $name,
                'phase' => $phase->value,
                'state' => $done ? TaskState::Finished->value : $upcomingState->value,
                'due_at' => $event->start_time,
            ]);
        }
    }
}
