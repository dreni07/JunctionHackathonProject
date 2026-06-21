<?php

declare(strict_types=1);

namespace App\Enums;

enum ConflictType: string
{
    case SpaceOverlap = 'space_overlap';
    case AssetShortage = 'asset_shortage';
    case SetupCollision = 'setup_collision';
    case ScheduleOverlap = 'schedule_overlap';

    public function label(): string
    {
        return match ($this) {
            self::SpaceOverlap => 'Space overlap',
            self::AssetShortage => 'Asset shortage',
            self::SetupCollision => 'Setup / teardown collision',
            self::ScheduleOverlap => 'Schedule overlap',
        };
    }
}
