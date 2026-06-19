<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lifecycle of an inbound event inquiry before it becomes a confirmed Event.
 */
enum EventRequestStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case ProposalDraft = 'proposal_draft';
    case Converted = 'converted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under review',
            self::ProposalDraft => 'Proposal in progress',
            self::Converted => 'Converted to event',
            self::Rejected => 'Rejected',
        };
    }
}
