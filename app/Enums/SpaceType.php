<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The kind of Pyramid space — the four main halls are "hall", with
 * transitional/outdoor areas modelled alongside them.
 */
enum SpaceType: string
{
    case Hall = 'hall';
    case WorkshopRoom = 'workshop_room';
    case Corridor = 'corridor';
    case Outdoor = 'outdoor';
    case HybridSpace = 'hybrid_space';

    public function label(): string
    {
        return match ($this) {
            self::Hall => 'Hall',
            self::WorkshopRoom => 'Workshop room',
            self::Corridor => 'Corridor',
            self::Outdoor => 'Outdoor',
            self::HybridSpace => 'Hybrid space',
        };
    }
}
