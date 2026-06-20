<?php

declare(strict_types=1);

namespace App\Agent\Neuron\Tools\Venue;

use App\Services\SchedulingService;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

/**
 * Given venue ids in ranked order and a time window, returns the first venue
 * with no calendar conflict. Backed by the deterministic {@see SchedulingService}.
 */
class SelectAvailableVenueTool extends Tool
{
    public function __construct(private readonly SchedulingService $scheduling)
    {
        parent::__construct(
            'select_available_venue',
            'Check the booking calendar and return the first venue (from the ranked ids, best first) '
                .'that has no reservation conflict for the given time window.',
        );
    }

    /**
     * @return list<ToolProperty>
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'space_ids',
                type: PropertyType::STRING,
                description: 'Comma-separated venue ids in ranked order, best first.',
                required: true,
            ),
            new ToolProperty(
                name: 'start_at',
                type: PropertyType::STRING,
                description: 'Event start date-time.',
                required: true,
            ),
            new ToolProperty(
                name: 'end_at',
                type: PropertyType::STRING,
                description: 'Event end date-time.',
                required: true,
            ),
        ];
    }

    public function __invoke(string $space_ids, string $start_at, string $end_at): string
    {
        $ids = array_values(array_filter(array_map('trim', explode(',', $space_ids))));

        return json_encode($this->scheduling->firstAvailable($ids, $start_at, $end_at)) ?: '{}';
    }
}
