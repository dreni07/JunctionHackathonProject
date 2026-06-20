<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Space;

/**
 * Scores every venue against an event and returns them ranked by confidence
 * (0-100, best first). This is the deterministic engine the venue-matching
 * agent's tool calls.
 */
class VenueMatchingService
{
    /**
     * Which room functional types best suit each event type.
     *
     * @var array<string, list<string>>
     */
    private const PREFERRED = [
        'conference' => ['Event Hall', 'Event Space', 'Tech Lab'],
        'workshop' => ['Classroom/Workshop', 'Tech Lab', 'Software Studio', 'Creative Studio'],
        'concert' => ['Event Hall', 'Event Space', 'Music Studio'],
        'exhibition' => ['Exhibition Space', 'Event Space'],
        'meetup' => ['Lounge', 'Tech Lab', 'Event Space', 'Cafe'],
        'hackathon' => ['Tech Lab', 'Event Hall', 'Event Space'],
        'performance' => ['Event Hall', 'Event Space', 'Film Studio', 'Music Studio'],
        'community_gathering' => ['Event Space', 'Lounge', 'Reception', 'Roof Event'],
        'private_event' => ['Event Space', 'Lounge', 'Roof Cafe', 'Roof Event'],
    ];

    /**
     * Ranked venues with the Space model attached (for persistence).
     *
     * @return list<array{space: Space, confidence: float}>
     */
    public function rank(string $eventType, int $attendees): array
    {
        $eventType = strtolower(trim($eventType));
        $preferred = self::PREFERRED[$eventType] ?? [];

        $results = [];

        foreach (Space::query()->whereNotNull('room_code')->get() as $space) {
            $confidence = $this->score($space, $attendees, $preferred);

            if ($confidence > 0) {
                $results[] = ['space' => $space, 'confidence' => $confidence];
            }
        }

        usort($results, fn (array $a, array $b): int => $b['confidence'] <=> $a['confidence']);

        return $results;
    }

    /**
     * Ranked venues as plain arrays (for tool output and the API response).
     *
     * @return list<array<string, mixed>>
     */
    public function rankAsArray(string $eventType, int $attendees): array
    {
        return array_map(
            fn (array $row): array => [
                'space_id' => $row['space']->id,
                'room_code' => $row['space']->room_code,
                'name' => $row['space']->name,
                'floor' => $row['space']->floor,
                'capacity' => $row['space']->capacity,
                'area_sqm' => $row['space']->area_sqm,
                'functional_type' => $row['space']->functional_type,
                'zone_class' => $row['space']->zone_class,
                'confidence' => $row['confidence'],
            ],
            $this->rank($eventType, $attendees),
        );
    }

    /**
     * Confidence (0-100) that a venue fits the event.
     *
     * @param  list<string>  $preferred
     */
    public function score(Space $space, int $attendees, array $preferred): float
    {
        // External organizations run commercial events — the TUMO education
        // zone is reserved for students, so those rooms are never offered.
        if ($space->zone_class === 'TUMO') {
            return 0.0;
        }

        // A venue that can't physically hold the crowd is disqualified.
        if ($space->capacity <= 0 || $attendees > $space->capacity) {
            return 0.0;
        }

        // Snug fits score higher than cavernous over-sized rooms.
        $capacityScore = $attendees / $space->capacity;

        // Type compatibility: a great fit, or a workable generic one.
        $typeScore = in_array($space->functional_type, $preferred, true) ? 1.0 : 0.45;

        return round(($capacityScore * 0.55 + $typeScore * 0.45) * 100, 1);
    }
}
