<?php

declare(strict_types=1);

namespace App\Agent\Tools;

use App\Agent\Tool;
use App\Enums\EventType;

/**
 * Lets the organizer change anything on the proposal after it has been shown —
 * the name, the kind of event, the headcount, the date/time, or the price.
 * It delegates to the same {@see ApiTool} so the change is reflected on the
 * user's screen and remembered when the request is finally submitted.
 */
class ReviseProposalTool implements Tool
{
    public function __construct(private readonly ApiTool $api) {}

    public function name(): string
    {
        return 'revise_proposal';
    }

    public function description(): string
    {
        return 'Change the proposal the organizer is looking at, after present_event_request has shown it. '
            .'Use it whenever they ask to change ANYTHING — "rename it to…", "make it 80 people", "move it to '
            .'Saturday", or "lower the price to 890". Pass only the fields that changed in "changes", and put a '
            .'newly negotiated price in "agreed_price". The screen updates and the new price is remembered for '
            .'submission. Do not start over with present_event_request for small changes — use this.';
    }

    /**
     * @return array<string, mixed>
     */
    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'changes' => [
                    'type' => 'object',
                    'description' => 'Only the fields the organizer wants to change.',
                    'properties' => [
                        'title' => ['type' => 'string'],
                        'event_type' => [
                            'type' => 'string',
                            'enum' => array_map(fn (EventType $c): string => $c->value, EventType::cases()),
                        ],
                        'description' => ['type' => 'string'],
                        'attendees' => ['type' => 'integer'],
                        'preferred_start_at' => ['type' => 'string', 'description' => 'ISO 8601 date-time'],
                        'preferred_end_at' => ['type' => 'string', 'description' => 'ISO 8601 date-time'],
                    ],
                ],
                'agreed_price' => [
                    'type' => 'number',
                    'description' => 'A new price in euros the organizer wants (e.g. they asked to lower it).',
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
        $changes = is_array($arguments['changes'] ?? null) ? $arguments['changes'] : [];
        $agreedPrice = isset($arguments['agreed_price']) && is_numeric($arguments['agreed_price'])
            ? (float) $arguments['agreed_price']
            : null;

        if ($changes === [] && $agreedPrice === null) {
            return 'Nothing to change — ask the organizer what they would like to adjust.';
        }

        return $this->api->revise($changes, $agreedPrice);
    }
}
