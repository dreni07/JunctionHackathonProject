<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Why a venue is unavailable: deliberately blocked, or out of service (broken).
 */
enum VenueUnavailabilityType: string
{
    case Blocked = 'blocked';
    case Broken = 'broken';

    public function label(): string
    {
        return match ($this) {
            self::Blocked => 'Blocked',
            self::Broken => 'Out of service',
        };
    }

    /**
     * A short, human reason an organizer can be told.
     */
    public function organizerReason(): string
    {
        return match ($this) {
            self::Blocked => 'it is blocked off and unavailable',
            self::Broken => 'it is currently out of service for maintenance',
        };
    }
}
