<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * State of a space reservation on the calendar. The conflict-checking agent
 * treats Tentative and Confirmed bookings as occupying the slot.
 */
enum BookingStatus: string
{
    case Tentative = 'tentative'; // held while a proposal is pending
    case Confirmed = 'confirmed'; // proposal accepted → event confirmed
    case Cancelled = 'cancelled'; // released, slot is free again

    public function label(): string
    {
        return match ($this) {
            self::Tentative => 'Tentative',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Statuses that actually occupy a slot for conflict detection.
     *
     * @return array<int, string>
     */
    public static function blocking(): array
    {
        return [self::Tentative->value, self::Confirmed->value];
    }
}
