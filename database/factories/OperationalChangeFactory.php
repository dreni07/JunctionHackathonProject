<?php

namespace Database\Factories;

use App\Models\OperationalChange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationalChange>
 */
class OperationalChangeFactory extends Factory
{
    protected $model = OperationalChange::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'model_type' => 'Asset',
            'model_id' => fake()->uuid(),
            'action' => 'updated',
            'summary' => 'Asset "Demo chair" was updated (status).',
            'payload' => [
                'model_type' => 'Asset',
                'model_id' => fake()->uuid(),
                'action' => 'updated',
                'changes' => ['status' => 'in_use'],
            ],
            'occurred_at' => now(),
        ];
    }
}
