<?php

declare(strict_types=1);

namespace App\Agent\Neuron;

use App\Agent\Neuron\Tools\Venue\RankVenuesTool;
use App\Services\VenueMatchingService;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Tools\ToolInterface;

/**
 * Matches an event to the Pyramid venues, producing a confidence score for
 * every venue (via its tool) and reporting the ranking.
 */
class VenueMatchingAgent extends OpenAIAgent
{
    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are the Pyramid of Tirana venue-matching agent.',
                'You decide how well each venue fits an event using a confidence score.',
            ],
            steps: [
                'Call rank_venues_by_fit with the event type and attendee count.',
                'The tool returns every suitable venue with a confidence value from 0 to 100, best first.',
            ],
            output: [
                'Briefly state the top venues and their confidence. Do not invent venues.',
            ],
        );
    }

    /**
     * @return list<ToolInterface>
     */
    protected function tools(): array
    {
        return [
            new RankVenuesTool(app(VenueMatchingService::class)),
        ];
    }
}
