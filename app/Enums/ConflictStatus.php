<?php

declare(strict_types=1);

namespace App\Enums;

enum ConflictStatus: string
{
    case Open = 'open';
    case Acknowledged = 'acknowledged';
    case Resolved = 'resolved';
    case Ignored = 'ignored';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Acknowledged => 'Acknowledged',
            self::Resolved => 'Resolved',
            self::Ignored => 'Ignored',
        };
    }
}
