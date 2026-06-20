<?php

declare(strict_types=1);

namespace App\Agent\Tools;

use App\Agent\Tool;
use App\Enums\EventType;
use App\Services\EventRequestService;
use App\Services\PricingService;
use App\Services\VenueOrchestrator;
use Throwable;

/**
 * Lets the intake agent make internal API calls. Two actions are supported:
 * presenting the assembled event request to the user for confirmation, and
 * creating (submitting) it. Both refuse to proceed until every required field
 * is present, so the agent can never submit an incomplete request.
 */
class ApiTool implements Tool
{
    /** @var array<string, mixed>|null */
    private ?array $review = null;

    /** @var array<string, mixed>|null */
    private ?array $submitted = null;

    public function __construct(
        private readonly EventRequestService $eventRequests,
        private readonly VenueOrchestrator $venues,
        private readonly PricingService $pricing,
        private readonly ?string $rawIntake = null,
    ) {}

    public function name(): string
    {
        return 'api_tool';
    }

    public function description(): string
    {
        return 'Make an internal API call about the event request. '
            .'action "present_event_request": once every required field is gathered, call this with the full '
            .'details to show the user a summary for confirmation, then ask them to confirm out loud. '
            .'action "create_event_request": submit and save the request — ONLY after the user has explicitly '
            .'confirmed. Both actions validate that all required fields are present.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['present_event_request', 'create_event_request'],
                ],
                'details' => [
                    'type' => 'object',
                    'description' => 'The assembled event request.',
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
                    'description' => 'On create_event_request only: the final price in euros the organizer '
                        .'agreed to. Omit to use the suggested price; set it if you negotiated a different figure.',
                ],
            ],
            'required' => ['action', 'details'],
        ];
    }

    public function execute(array $arguments): string
    {
        $action = (string) ($arguments['action'] ?? '');
        $details = is_array($arguments['details'] ?? null) ? $arguments['details'] : [];
        $agreedPrice = isset($arguments['agreed_price']) && is_numeric($arguments['agreed_price'])
            ? (float) $arguments['agreed_price']
            : null;

        $normalized = $this->eventRequests->normalize($details);

        if (! $normalized['ok']) {
            return 'Not ready for '.$action.' — keep asking the user until these are resolved: '
                .implode('; ', $normalized['errors']);
        }

        return match ($action) {
            'present_event_request' => $this->present($normalized['data']),
            'create_event_request' => $this->submit($details, $agreedPrice),
            default => 'Unknown action. Use present_event_request or create_event_request.',
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function present(array $data): string
    {
        // Hand off to the matching + scheduling agents to pick a venue.
        $recommendation = $this->venues->recommend(
            (string) $data['event_type'],
            (int) $data['attendees'],
            (string) $data['preferred_start_at'],
            (string) $data['preferred_end_at'],
        );

        $venue = $recommendation['selected'];
        $reason = $recommendation['reason'];

        if ($venue === null) {
            $this->review = [
                ...$data,
                'venue' => null,
                'pricing' => null,
                'reason' => $reason,
                'max_capacity' => $recommendation['max_capacity'] ?? null,
            ];

            // Explain the REAL reason so you can be honest with the user.
            if ($reason === 'over_capacity') {
                $max = (int) ($recommendation['max_capacity'] ?? 0);

                return 'Reasoning: the event expects '.$data['attendees'].' people, but the largest space the '
                    .'Pyramid can rent for an event holds about '.$max.'. No venue can physically fit this '
                    .'crowd. Do NOT say it is a calendar problem. Explain honestly that '.$data['attendees']
                    .' exceeds the '.$max.'-person capacity of the biggest space, and suggest lowering the '
                    .'headcount to around '.$max.' or fewer, or splitting the event across more than one space '
                    .'or day.';
            }

            return 'Reasoning: venues of the right size exist, but every one of them is already booked for '
                .'that exact date and time. Explain that the suitable spaces are taken then, and ask the user '
                .'for a different day or time.';
        }

        // Suggest a price for the matched venue from past pricing data.
        $duration = $this->pricing->durationDaysBetween(
            (string) $data['preferred_start_at'],
            (string) $data['preferred_end_at'],
        );
        $pricing = $this->pricing->suggest(
            (string) $data['event_type'],
            (int) ($venue['area_sqm'] ?? 0),
            $duration,
        );

        $this->review = [...$data, 'venue' => $venue, 'pricing' => $pricing, 'reason' => 'ok'];

        $priceLine = $pricing !== null
            ? ' The suggested price is about €'.number_format($pricing['total'], 0)
                .' (€'.number_format($pricing['price_per_sqm'], 2).' per square metre), based on similar past events.'
            : '';

        return 'Reasoning: this venue holds '.($venue['capacity'] ?? '?').' and the event expects '
            .$data['attendees'].' people, so it fits (match confidence '.$venue['confidence'].'%). The event '
            .'summary, the recommended venue ('.$venue['name'].') and the suggested price are now on the '
            .'user\'s screen.'.$priceLine.' Tell them which venue you recommend and why it suits their event, '
            .'give the suggested price, then ask them to confirm out loud (e.g. "send the event request") '
            .'before you submit it.';
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function submit(array $details, ?float $agreedPrice): string
    {
        try {
            $eventRequest = $this->eventRequests->create($details, $this->rawIntake, $agreedPrice);
        } catch (Throwable $e) {
            return 'Submission failed: '.$e->getMessage();
        }

        $agreed = $eventRequest->price_agreed !== null ? (float) $eventRequest->price_agreed : null;

        $this->submitted = [
            'id' => $eventRequest->id,
            'status' => $eventRequest->status->value,
            'price' => $agreed,
        ];

        $priceLine = $agreed !== null ? ' The agreed price is €'.number_format($agreed, 0).'.' : '';

        return 'The event request was created and saved (id '.$eventRequest->id.', status submitted).'
            .$priceLine.' Tell the user it is done'.($agreed !== null ? ' and confirm the agreed price.' : '.');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function review(): ?array
    {
        return $this->review;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function submitted(): ?array
    {
        return $this->submitted;
    }
}
