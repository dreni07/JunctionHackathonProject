<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lifecycle of a proposal made to the Pyramid. Only once a proposal is
 * Accepted is a real Event instance created from it.
 */
enum ProposalStatus: string
{
    case Draft = 'draft';        // being prepared by the agent / staff
    case Sent = 'sent';          // submitted to the Pyramid for a decision
    case Accepted = 'accepted';  // approved → an Event gets created
    case Rejected = 'rejected';  // declined by the Pyramid
    case Withdrawn = 'withdrawn'; // pulled back before a decision

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
            self::Withdrawn => 'Withdrawn',
        };
    }
}
