<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How risky the agent considers an event's current plan (missing info,
 * tight resources, scheduling conflicts, ...).
 */
enum RiskLevel: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
        };
    }
}
