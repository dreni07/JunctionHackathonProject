<?php

declare(strict_types=1);

namespace App\Agent\Neuron;

use App\Agent\Neuron\Tools\Venue\SelectAvailableVenueTool;
use App\Services\SchedulingService;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Tools\ToolInterface;

/**
 * Takes the ranked venues and the requested time window and finds the first one
 * that is free on the booking calendar (via its tool).
 */
class SchedulingAgent extends OpenAIAgent
{
    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are the Pyramid of Tirana scheduling agent.',
                'You pick the best venue that is actually free on the booking calendar.',
            ],
            steps: [
                'Call select_available_venue with the ranked venue ids (best first), the start time and the end time.',
                'The tool walks the ranked list and returns the first venue with no calendar conflict.',
            ],
            output: [
                'State which venue is available, or that none of the ranked venues are free for that time.',
            ],
        );
    }

    /**
     * @return list<ToolInterface>
     */
    protected function tools(): array
    {
        return [
            new SelectAvailableVenueTool(app(SchedulingService::class)),
        ];
    }
}
