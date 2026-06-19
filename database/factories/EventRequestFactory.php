<?php

namespace Database\Factories;

use App\Enums\EventRequestStatus;
use App\Enums\EventType;
use App\Models\EventRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRequest>
 */
class EventRequestFactory extends Factory
{
    protected $model = EventRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 week', '+2 months');

        return [
            'submitted_by' => User::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'event_type' => $this->faker->randomElement(EventType::cases()),
            'attendees' => $this->faker->numberBetween(30, 250),
            'preferred_start_at' => $start,
            'preferred_end_at' => (clone $start)->modify('+4 hours'),
            'raw_intake' => $this->faker->paragraph(),
            'status' => EventRequestStatus::Submitted,
        ];
    }
}
