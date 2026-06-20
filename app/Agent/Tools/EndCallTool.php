<?php

declare(strict_types=1);

namespace App\Agent\Tools;

use App\Agent\Tool;

/**
 * Lets the agent end the conversation itself once everything is done, instead
 * of waiting for the user to hang up.
 */
class EndCallTool implements Tool
{
    private bool $ended = false;

    public function name(): string
    {
        return 'end_call';
    }

    public function description(): string
    {
        return 'End the conversation. Call this only when everything is finished — the event request has '
            .'been submitted (or the organizer has confirmed they need nothing more, or they say goodbye) — '
            .'and you are about to give a short, warm farewell. You can close the call yourself; you do not '
            .'have to wait for the user to hang up.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'reason' => [
                    'type' => 'string',
                    'description' => 'A brief reason the call is ending (e.g. "request submitted").',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $arguments): string
    {
        $this->ended = true;

        return 'The call will end right after your farewell. Give a short, warm goodbye now '
            .'(one sentence) and say nothing else.';
    }

    public function ended(): bool
    {
        return $this->ended;
    }
}
