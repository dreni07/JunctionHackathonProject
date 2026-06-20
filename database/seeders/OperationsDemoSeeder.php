<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\EventRequestService;
use Illuminate\Database\Seeder;

/**
 * A small slice of operational life — events, tasks assigned to tenant workers,
 * alerts, and pending event requests — so the operations dashboard has real
 * data. Each section seeds only when its table is empty.
 */
class OperationsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPendingRequests();
        $this->seedEventsTasksAndAlerts();
    }

    /**
     * A few unconfirmed event requests for the operations dashboard to triage.
     */
    private function seedPendingRequests(): void
    {
        if (EventRequest::query()->exists()) {
            return;
        }

        $samples = [
            ['Balkan Founders Mixer', 'meetup', 'Networking for startup founders', 80, '+90 days', 18, 21],
            ['AI Builders Conference', 'conference', 'A day of AI talks and demos', 110, '+94 days', 9, 17],
            ['Open Source Hack Night', 'hackathon', 'Evening community hackathon', 60, '+97 days', 17, 23],
        ];

        $service = app(EventRequestService::class);

        foreach ($samples as [$title, $type, $description, $attendees, $offset, $startHour, $endHour]) {
            $day = now()->modify($offset);

            $service->create([
                'title' => $title,
                'event_type' => $type,
                'description' => $description,
                'attendees' => $attendees,
                'preferred_start_at' => $day->copy()->setTime($startHour, 0)->toIso8601String(),
                'preferred_end_at' => $day->copy()->setTime($endHour, 0)->toIso8601String(),
            ], 'Seeded demo request');
        }
    }

    private function seedEventsTasksAndAlerts(): void
    {
        if (Event::query()->exists()) {
            return;
        }

        $organizer = User::query()->where('account_type', 'organization')->orderBy('id')->first();
        $workers = User::query()->where('account_type', 'operational')->orderBy('id')->take(6)->get();

        if ($organizer === null || $workers->isEmpty()) {
            return;
        }

        $now = now();

        $events = collect([
            ['Tirana Tech Summit', 'conference', 'active', 220, 3],
            ['ICT Demo Day', 'meetup', 'planning', 90, 8],
            ['Youth Robotics Showcase', 'exhibition', 'approved', 140, 14],
        ])->map(fn (array $e): Event => Event::create([
            'title' => $e[0],
            'event_type' => $e[1],
            'status' => $e[2],
            'attendees' => $e[3],
            'start_time' => $now->addDays($e[4])->setTime(9, 0),
            'end_time' => $now->addDays($e[4])->setTime(17, 0),
            'created_by' => $organizer->id,
        ]));

        // [name, phase, state, due in days]
        $tasks = [
            ['Confirm room setup and seating', 'setup', 'started', 0],
            ['Test AV and microphones', 'setup', 'pending', 0],
            ['Coordinate catering delivery', 'setup', 'ongoing', 1],
            ['Brief volunteers on the schedule', 'during', 'pending', 2],
            ['Manage the registration desk', 'during', 'pending', 3],
            ['Pack and store equipment', 'teardown', 'pending', 7],
            ['Collect feedback forms', 'teardown', 'pending', 7],
            ['Reset lighting configuration', 'teardown', 'finished', -1],
        ];

        foreach ($tasks as $i => $task) {
            Task::create([
                'event_id' => $events[$i % $events->count()]->id,
                'user_id' => $workers[$i % $workers->count()]->id,
                'name' => $task[0],
                'phase' => $task[1],
                'state' => $task[2],
                'due_at' => $now->addDays($task[3])->setTime(17, 0),
            ]);
        }

        // [user_id, source, severity, status, title, message, agent]
        $alerts = [
            [null, 'conflict', 'high', 'unread', 'Double booking detected', 'Two events overlap in the main event hall on Saturday evening.', 'Scheduling Agent'],
            [$workers[0]->id, 'agent', 'medium', 'unread', 'Task due today', 'Your room setup task for Tirana Tech Summit is due today.', 'Ops Copilot'],
            [null, 'inventory', 'medium', 'unread', 'Low microphone stock', 'Only 4 microphones are available across 3 concurrent events.', 'Inventory Agent'],
            [$workers[0]->id, 'schedule', 'low', 'read', 'Shift reminder', 'Your shift starts at 8:00 tomorrow.', 'System'],
            [null, 'system', 'low', 'unread', 'Maintenance window', 'The nightly facility lock runs 23:00 to 06:00.', 'System'],
        ];

        foreach ($alerts as $alert) {
            Alert::create([
                'user_id' => $alert[0],
                'source' => $alert[1],
                'severity' => $alert[2],
                'status' => $alert[3],
                'title' => $alert[4],
                'message' => $alert[5],
                'agent_name' => $alert[6],
                'read_at' => $alert[3] === 'read' ? $now : null,
            ]);
        }

        $this->command?->info('Seeded '.$events->count().' events, '.count($tasks).' tasks, '.count($alerts).' alerts.');
    }
}
