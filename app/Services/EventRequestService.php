<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\EventRequestStatus;
use App\Enums\EventType;
use App\Models\EventRequest;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use App\Models\VenueMatch;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Validates and persists event requests gathered by the intake agent (the
 * "source of truth"). Shared by the HTTP endpoint and the agent's api_tool.
 */
class EventRequestService
{
    /** @var list<string> */
    public const REQUIRED_FIELDS = [
        'title',
        'event_type',
        'description',
        'attendees',
        'preferred_start_at',
        'preferred_end_at',
    ];

    public function __construct(
        private readonly VenueMatchingService $matching,
        private readonly SchedulingService $scheduling,
        private readonly PricingService $pricing,
    ) {}

    /**
     * Validate and normalize a raw details payload.
     *
     * @param  array<string, mixed>  $details
     * @return array{ok: bool, errors: list<string>, data: array<string, mixed>}
     */
    public function normalize(array $details): array
    {
        $errors = [];
        $data = [];

        $title = trim((string) ($details['title'] ?? ''));
        $title === '' ? $errors[] = 'title is missing' : $data['title'] = $title;

        $description = trim((string) ($details['description'] ?? ''));
        $description === '' ? $errors[] = 'description is missing' : $data['description'] = $description;

        $type = str_replace([' ', '-'], '_', strtolower(trim((string) ($details['event_type'] ?? ''))));
        $eventType = EventType::tryFrom($type);

        if (! $eventType instanceof EventType) {
            $errors[] = 'event_type must be one of: '.implode(', ', array_map(fn (EventType $c): string => $c->value, EventType::cases()));
        } else {
            $data['event_type'] = $eventType->value;
        }

        $attendees = is_numeric($details['attendees'] ?? null) ? (int) $details['attendees'] : 0;
        $attendees <= 0 ? $errors[] = 'attendees must be a positive number' : $data['attendees'] = $attendees;

        $start = $this->parseDate($details['preferred_start_at'] ?? null);
        $start === null ? $errors[] = 'preferred_start_at is missing or not a valid date/time' : $data['preferred_start_at'] = $start->toIso8601String();

        $end = $this->parseDate($details['preferred_end_at'] ?? null);
        $end === null ? $errors[] = 'preferred_end_at is missing or not a valid date/time' : $data['preferred_end_at'] = $end->toIso8601String();

        if ($start !== null && $end !== null && $end->lessThanOrEqualTo($start)) {
            $errors[] = 'preferred_end_at must be after preferred_start_at';
        }

        return ['ok' => $errors === [], 'errors' => $errors, 'data' => $data];
    }

    /**
     * Create the event request from gathered details.
     *
     * @param  array<string, mixed>  $details
     */
    public function create(array $details, ?string $rawIntake = null, ?float $agreedPrice = null): EventRequest
    {
        $normalized = $this->normalize($details);

        if (! $normalized['ok']) {
            throw new InvalidArgumentException('Invalid event request: '.implode('; ', $normalized['errors']));
        }

        $submitter = $this->resolveSubmitter();
        $data = $normalized['data'];

        return DB::transaction(function () use ($data, $rawIntake, $submitter, $agreedPrice): EventRequest {
            // Match every venue, then pick the highest-confidence one that is
            // free on the calendar.
            $ranked = $this->matching->rank($data['event_type'], $data['attendees']);
            $ids = array_map(fn (array $row): string => $row['space']->id, $ranked);
            $schedule = $this->scheduling->firstAvailable(
                $ids,
                $data['preferred_start_at'],
                $data['preferred_end_at'],
            );
            $selectedId = $schedule['selected_space_id'];

            $eventRequest = EventRequest::create([
                ...$data,
                'organization_id' => $submitter['organization_id'],
                'submitted_by' => $submitter['user_id'],
                'matched_space_id' => $selectedId,
                'raw_intake' => $rawIntake,
                'status' => EventRequestStatus::Submitted->value,
            ]);

            // Persist the matching agent's full output.
            $availability = collect($schedule['checked'])->keyBy('space_id');
            $rank = 1;

            foreach ($ranked as $row) {
                $spaceId = $row['space']->id;

                VenueMatch::create([
                    'event_request_id' => $eventRequest->id,
                    'space_id' => $spaceId,
                    'confidence_score' => $row['confidence'],
                    'rank' => $rank++,
                    'available' => (bool) ($availability[$spaceId]['available'] ?? false),
                    'selected' => $spaceId === $selectedId,
                ]);
            }

            // Hold the chosen slot on the calendar so future requests see it,
            // and suggest a price from the pricing dataset.
            if ($selectedId !== null) {
                Reservation::create([
                    'space_id' => $selectedId,
                    'start_at' => $data['preferred_start_at'],
                    'end_at' => $data['preferred_end_at'],
                    'status' => BookingStatus::Tentative->value,
                ]);

                /** @var Space|null $venue */
                $venue = collect($ranked)->firstWhere(fn (array $row): bool => $row['space']->id === $selectedId)['space'] ?? null;

                if ($venue !== null && $venue->area_sqm > 0) {
                    $duration = $this->pricing->durationDaysBetween(
                        $data['preferred_start_at'],
                        $data['preferred_end_at'],
                    );
                    $suggestion = $this->pricing->suggest($data['event_type'], $venue->area_sqm, $duration);

                    if ($suggestion !== null) {
                        $suggestedTotal = $suggestion['total'];
                        // The agreed price is what the organizer settled on — the
                        // suggestion unless they negotiated a different figure.
                        $agreedTotal = $agreedPrice !== null && $agreedPrice > 0
                            ? round($agreedPrice, 2)
                            : $suggestedTotal;
                        $negotiated = abs($agreedTotal - $suggestedTotal) >= 0.01;

                        $eventRequest->update([
                            'price_suggested' => $suggestedTotal,
                            'price_agreed' => $agreedTotal,
                            'price_per_sqm' => round($agreedTotal / $venue->area_sqm, 2),
                        ]);

                        // Feed the AGREED price back into the dataset so future
                        // suggestions learn from what organizers actually accept.
                        $this->pricing->record([
                            'organizer' => $eventRequest->title,
                            'event_type' => $data['event_type'],
                            'venue_name' => $venue->name,
                            'floor' => $venue->floor,
                            'area_sqm' => $venue->area_sqm,
                            'duration_days' => $duration,
                            'attendees' => $data['attendees'],
                            'price_eur' => $agreedTotal,
                            'notes' => $negotiated
                                ? 'Agreed after negotiation (suggested €'.number_format($suggestedTotal, 0).').'
                                : 'Agent-suggested price, accepted by the organizer.',
                            'event_request_id' => $eventRequest->id,
                        ]);
                    }
                }
            }

            return $eventRequest;
        });
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * The submitter is the signed-in user, or a fallback organization account
     * for the public voice planner (submitted_by is required on the table).
     *
     * @return array{user_id: int, organization_id: string|null}
     */
    private function resolveSubmitter(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            $user = User::query()->where('account_type', 'organization')->orderBy('id')->first()
                ?? User::query()->orderBy('id')->first();
        }

        if (! $user instanceof User) {
            throw new RuntimeException('No user available to submit the event request.');
        }

        return ['user_id' => $user->id, 'organization_id' => $user->organization_id];
    }
}
