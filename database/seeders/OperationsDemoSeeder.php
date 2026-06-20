<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * A small slice of operational life — events, tasks assigned to tenant workers,
 * and alerts — so the operations dashboard has real data. Seeds only when empty.
 */
class OperationsDemoSeeder extends Seeder
{
    public function run(): void
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
