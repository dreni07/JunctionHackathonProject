<?php

declare(strict_types=1);

namespace App\Agent\Neuron\Tools\Venue;

use App\Services\VenueMatchingService;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

/**
 * Scores every Pyramid venue against the event and returns them ranked by
 * confidence. Backed by the deterministic {@see VenueMatchingService}.
 */
class RankVenuesTool extends Tool
{
    public function __construct(private readonly VenueMatchingService $matching)
    {
        parent::__construct(
            'rank_venues_by_fit',
            'Score every venue for how well it fits this event and return them ranked by a '
                .'confidence value from 0 to 100, best first. Excludes venues that cannot host the event.',
        );
    }

    /**
     * @return list<ToolProperty>
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'event_type',
                type: PropertyType::STRING,
                description: 'The kind of event (conference, workshop, hackathon, exhibition, ...).',
                required: true,
            ),
            new ToolProperty(
                name: 'attendees',
                type: PropertyType::INTEGER,
                description: 'Expected number of attendees.',
                required: true,
            ),
        ];
    }

    public function __invoke(string $event_type, int $attendees): string
    {
        $ranked = $this->matching->rankAsArray($event_type, $attendees);

        return json_encode($ranked) ?: '[]';
    }
}
