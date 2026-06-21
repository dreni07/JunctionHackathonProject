<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Reservation;
use App\Models\VenueUnavailability;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * Reads the booking calendar (reservations) to find the first venue, in ranked
 * order, that is free for the requested time window. A conflict means an
 * existing blocking reservation overlaps the same space and time.
 */
class SchedulingService
{
    /**
     * Walk the ranked venue ids and return the first one with no calendar
     * conflict (while recording availability for all of them).
     *
     * @param  list<string>  $spaceIds  Venue ids, best first.
     * @return array{selected_space_id: string|null, checked: list<array{space_id: string, available: bool}>}
     */
    public function firstAvailable(array $spaceIds, string $start, string $end): array
    {
        $startAt = $this->parse($start);
        $endAt = $this->parse($end);

        if ($startAt === null || $endAt === null) {
            return ['selected_space_id' => null, 'checked' => []];
        }

        $checked = [];
        $selected = null;

        foreach ($spaceIds as $spaceId) {
            $available = $this->isAvailable($spaceId, $startAt, $endAt);
            $checked[] = ['space_id' => $spaceId, 'available' => $available];

            if ($available && $selected === null) {
                $selected = $spaceId;
            }
        }

        return ['selected_space_id' => $selected, 'checked' => $checked];
    }

    /**
     * Whether a space has no blocking reservation overlapping the window.
     */
    public function isAvailable(string $spaceId, CarbonImmutable $start, CarbonImmutable $end): bool
    {
        $booked = Reservation::query()
            ->where('space_id', $spaceId)
            ->blocking()
            ->overlapping($start->toDateTimeString(), $end->toDateTimeString())
            ->exists();

        // A blocked or out-of-service venue is just as unavailable as a booked one.
        return ! $booked && $this->unavailabilityFor($spaceId, $start, $end) === null;
    }

    /**
     * The block / out-of-service spell that makes a venue unusable for the
     * given window, or null if none — used to tell the organizer the reason.
     */
    public function unavailabilityFor(string $spaceId, CarbonImmutable $start, CarbonImmutable $end): ?VenueUnavailability
    {
        return VenueUnavailability::query()
            ->where('space_id', $spaceId)
            ->overlapping($start, $end)
            ->orderByRaw("CASE WHEN type = 'broken' THEN 0 ELSE 1 END")
            ->first();
    }

    private function parse(string $value): ?CarbonImmutable
    {
        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
