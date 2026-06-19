<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityLogAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case StatusChanged = 'status_changed';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case ProposalSent = 'proposal_sent';
    case AssetReserved = 'asset_reserved';
    case ConflictDetected = 'conflict_detected';
    case ConflictResolved = 'conflict_resolved';
    case TaskAssigned = 'task_assigned';
    case Converted = 'converted';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Created',
            self::Updated => 'Updated',
            self::StatusChanged => 'Status changed',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::ProposalSent => 'Proposal sent',
            self::AssetReserved => 'Asset reserved',
            self::ConflictDetected => 'Conflict detected',
            self::ConflictResolved => 'Conflict resolved',
            self::TaskAssigned => 'Task assigned',
            self::Converted => 'Converted to event',
        };
    }
}
