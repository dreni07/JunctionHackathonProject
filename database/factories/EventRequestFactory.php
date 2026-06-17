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
        $start = fake()->dateTimeBetween('+1 week', '+2 months');

        return [
            'submitted_by' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'event_type' => fake()->randomElement(EventType::cases()),
            'attendees' => fake()->numberBetween(30, 250),
            'preferred_start_at' => $start,
            'preferred_end_at' => (clone $start)->modify('+4 hours'),
            'raw_intake' => fake()->paragraph(),
            'status' => EventRequestStatus::Submitted,
        ];
    }
}
