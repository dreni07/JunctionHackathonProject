<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Workflow state of an operational task assigned to a Pyramid worker.
 */
enum TaskState: string
{
    case Pending = 'pending';        // created, not started yet
    case Started = 'started';
    case Ongoing = 'ongoing';
    case OnProcess = 'on_process';
    case Finished = 'finished';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Started => 'Started',
            self::Ongoing => 'Ongoing',
            self::OnProcess => 'On process',
            self::Finished => 'Finished',
            self::Cancelled => 'Cancelled',
        };
    }
}
