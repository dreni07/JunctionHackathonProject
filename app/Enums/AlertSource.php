<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Where an operational alert originated.
 */
enum AlertSource: string
{
    case Agent = 'agent';
    case System = 'system';
    case Conflict = 'conflict';
    case Inventory = 'inventory';
    case Schedule = 'schedule';

    public function label(): string
    {
        return match ($this) {
            self::Agent => 'AI agent',
            self::System => 'System',
            self::Conflict => 'Conflict detection',
            self::Inventory => 'Inventory',
            self::Schedule => 'Schedule',
        };
    }
}
