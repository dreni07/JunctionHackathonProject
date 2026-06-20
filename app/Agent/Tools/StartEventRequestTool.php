<?php

declare(strict_types=1);

namespace App\Agent\Tools;

use App\Agent\Tool;

/**
 * Lets the event advisor hand the conversation over to the booking flow once
 * the visitor clearly wants to actually organize an event. Calling it flips the
 * conversation from "advisor" mode to the event-intake agent.
 */
class StartEventRequestTool implements Tool
{
    private bool $escalated = false;

    public function name(): string
    {
        return 'start_event_request';
    }

    public function description(): string
    {
        return 'Hand the visitor over to the event-booking assistant. Call this ONLY when the person clearly '
            .'wants to actually organize or book an event — for example they say "I want to book", "let\'s set '
            .'it up", "how do I reserve", "I\'d like to go ahead", or they start giving the details of an event '
            .'they want to hold. Do NOT call it while they are only asking questions or exploring options.';
    }

    /**
     * @return array<string, mixed>
     */
    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'reason' => [
                    'type' => 'string',
                    'description' => 'A brief note on what the visitor wants to organize.',
                ],
            ],
            'required' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function execute(array $arguments): string
    {
        $this->escalated = true;

        return 'Handing over to the booking assistant now.';
    }

    public function escalated(): bool
    {
        return $this->escalated;
    }
}
