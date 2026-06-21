<?php

namespace Database\Factories;

use App\Enums\AlertSource;
use App\Enums\AlertStatus;
use App\Enums\RiskLevel;
use App\Models\Alert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => AlertSource::Agent,
            'severity' => $this->faker->randomElement(RiskLevel::cases()),
            'status' => AlertStatus::Unread,
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(),
            'agent_name' => 'FeasibilityAgent',
        ];
    }
}
