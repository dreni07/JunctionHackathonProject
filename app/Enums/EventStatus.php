<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lifecycle of an event — the single source of truth's status field.
 */
enum EventStatus: string
{
    case Draft = 'draft';
    case CollectingInfo = 'collecting_info';
    case Planning = 'planning';
    case Approved = 'approved';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::CollectingInfo => 'Collecting info',
            self::Planning => 'Planning',
            self::Approved => 'Approved',
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }
}
