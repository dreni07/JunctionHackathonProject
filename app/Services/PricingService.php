<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PricingReference;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * Suggests an event price per square metre by learning from the pricing
 * reference dataset (past events + the agent's own accepted suggestions), and
 * records new accepted suggestions back into that dataset.
 */
class PricingService
{
    /**
     * Suggest a price for an event based on comparable past events.
     *
     * @return array{price_per_sqm: float, total: float, sample_size: int, basis: string}|null
     */
    public function suggest(string $eventType, int $areaSqm, int $durationDays): ?array
    {
        if ($areaSqm <= 0) {
            return null;
        }

        $durationDays = max(1, $durationDays);

        $references = PricingReference::query()->where('area_sqm', '>', 0)->get();

        if ($references->isEmpty()) {
            return null;
        }

        // Prefer events of the same kind; fall back to the whole dataset.
        $keyword = explode(' ', str_replace('_', ' ', strtolower(trim($eventType))))[0];
        $comparable = $references->filter(
            fn (PricingReference $r): bool => $keyword !== '' && str_contains(strtolower($r->event_type), $keyword),
        );

        $basis = "comparable {$eventType} events";

        if ($comparable->isEmpty()) {
            $comparable = $references;
            $basis = 'all past events';
        }

        // Average rate per m² per day, then scale to this booking.
        $avgPerSqmPerDay = $comparable
            ->map(fn (PricingReference $r): float => (float) $r->price_eur / ($r->area_sqm * max(1, $r->duration_days)))
            ->avg();

        $pricePerSqm = round((float) $avgPerSqmPerDay * $durationDays, 2);
        $total = round($pricePerSqm * $areaSqm, 2);

        return [
            'price_per_sqm' => $pricePerSqm,
            'total' => $total,
            'sample_size' => $comparable->count(),
            'basis' => $basis,
        ];
    }

    /**
     * Append an accepted suggestion to the dataset so future suggestions learn
     * from it.
     *
     * @param  array<string, mixed>  $data
     */
    public function record(array $data): PricingReference
    {
        $area = max(1, (int) ($data['area_sqm'] ?? 1));
        $price = (float) ($data['price_eur'] ?? 0);

        return PricingReference::create([
            'source' => 'agent',
            'organizer' => $data['organizer'] ?? null,
            'event_type' => (string) ($data['event_type'] ?? 'event'),
            'venue_name' => $data['venue_name'] ?? null,
            'floor' => isset($data['floor']) ? (string) $data['floor'] : null,
            'area_sqm' => $area,
            'duration_days' => max(1, (int) ($data['duration_days'] ?? 1)),
            'attendees' => (int) ($data['attendees'] ?? 0),
            'price_eur' => round($price, 2),
            'price_per_sqm' => round($price / $area, 2),
            'notes' => $data['notes'] ?? 'Agent-suggested price, accepted by the organizer.',
            'event_request_id' => $data['event_request_id'] ?? null,
        ]);
    }

    /**
     * Calendar-day span of a booking (same day counts as 1).
     */
    public function durationDaysBetween(string $start, string $end): int
    {
        try {
            $startDay = CarbonImmutable::parse($start)->startOfDay();
            $endDay = CarbonImmutable::parse($end)->startOfDay();

            return max(1, $startDay->diffInDays($endDay) + 1);
        } catch (Throwable) {
            return 1;
        }
    }
}
