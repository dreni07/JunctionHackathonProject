<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Current state of a physical asset in the QR tracking system.
 */
enum AssetStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case InUse = 'in_use';
    case Maintenance = 'maintenance';
    case Missing = 'missing';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Reserved => 'Reserved',
            self::InUse => 'In use',
            self::Maintenance => 'Maintenance',
            self::Missing => 'Missing',
        };
    }
}
