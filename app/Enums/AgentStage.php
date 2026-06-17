<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The agent's processing stage for an event — the "brain snapshot" that
 * tracks how far the AI has progressed from intake to a ready plan.
 */
enum AgentStage: string
{
    case Collecting = 'collecting';
    case Validating = 'validating';
    case Planning = 'planning';
    case Executing = 'executing';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Collecting => 'Collecting',
            self::Validating => 'Validating',
            self::Planning => 'Planning',
            self::Executing => 'Executing',
            self::Done => 'Done',
        };
    }
}
