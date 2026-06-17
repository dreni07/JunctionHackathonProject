<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The kinds of events the Pyramid of Tirana hosts. The string value is what
 * gets stored on the event_requests table.
 */
enum EventType: string
{
    case Conference = 'conference';
    case Workshop = 'workshop';
    case Concert = 'concert';
    case Exhibition = 'exhibition';
    case Meetup = 'meetup';
    case Hackathon = 'hackathon';
    case Performance = 'performance';
    case CommunityGathering = 'community_gathering';
    case PrivateEvent = 'private_event';

    public function label(): string
    {
        return match ($this) {
            self::Conference => 'Conference',
            self::Workshop => 'Workshop',
            self::Concert => 'Concert',
            self::Exhibition => 'Exhibition',
            self::Meetup => 'Meetup',
            self::Hackathon => 'Hackathon',
            self::Performance => 'Performance',
            self::CommunityGathering => 'Community gathering',
            self::PrivateEvent => 'Private event',
        };
    }
}
