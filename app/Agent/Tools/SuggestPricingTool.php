<?php

declare(strict_types=1);

namespace App\Agent\Tools;

use App\Agent\Tool;
use App\Services\PricingService;

/**
 * Gives the organizer a price suggestion (per m² + total) for their event,
 * learned from what comparable past events at the Pyramid have paid.
 */
class SuggestPricingTool implements Tool
{
    public function __construct(private readonly PricingService $pricing) {}

    public function name(): string
    {
        return 'suggest_pricing';
    }

    public function description(): string
    {
        return 'Suggest a fair price for the event based on what comparable past events at the Pyramid '
            .'have paid. Provide the event type, the venue floor area in square metres, and how many days '
            .'the event runs. Returns a suggested total price and a price per square metre.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'event_type' => [
                    'type' => 'string',
                    'description' => 'The kind of event (conference, workshop, exhibition, ...).',
                ],
                'area_sqm' => [
                    'type' => 'integer',
                    'description' => 'The venue floor area in square metres.',
                ],
                'duration_days' => [
                    'type' => 'integer',
                    'description' => 'How many days the event runs (default 1).',
                ],
            ],
            'required' => ['event_type', 'area_sqm'],
        ];
    }

    public function execute(array $arguments): string
    {
        $type = trim((string) ($arguments['event_type'] ?? ''));
        $area = (int) ($arguments['area_sqm'] ?? 0);
        $duration = max(1, (int) ($arguments['duration_days'] ?? 1));

        if ($type === '' || $area <= 0) {
            return 'Provide an event type and a positive venue area in square metres.';
        }

        $suggestion = $this->pricing->suggest($type, $area, $duration);

        if ($suggestion === null) {
            return 'No pricing history is available to base a suggestion on yet.';
        }

        return 'Suggested price: about €'.number_format($suggestion['total'], 0).' total '
            .'(€'.number_format($suggestion['price_per_sqm'], 2).' per m²), based on '
            .$suggestion['sample_size'].' '.$suggestion['basis'].'.';
    }
}
