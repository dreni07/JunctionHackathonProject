<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskPhase: string
{
    case Setup = 'setup';
    case During = 'during';
    case Teardown = 'teardown';

    public function label(): string
    {
        return match ($this) {
            self::Setup => 'Setup',
            self::During => 'During event',
            self::Teardown => 'Teardown',
        };
    }
}
