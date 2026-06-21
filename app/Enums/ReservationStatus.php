<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * State of an asset reservation against an event.
 */
enum ReservationStatus: string
{
    case Reserved = 'reserved';
    case Released = 'released';
    case Consumed = 'consumed';

    public function label(): string
    {
        return match ($this) {
            self::Reserved => 'Reserved',
            self::Released => 'Released',
            self::Consumed => 'Consumed',
        };
    }
}
