<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Authorization\Permissions;
use App\Enums\ActivityLogAction;
use App\Enums\BookingStatus;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\SchedulingService;
use App\Support\OperationalAccess;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use RuntimeException;

class ReservationManagementService
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly SchedulingService $scheduling,
    ) {}

    /**
     * @return Collection<int, Reservation>
     */
    public function listForEvent(User $user, Event $event): Collection
    {
        OperationalAccess::ensureCanViewEvent($user, $event);

        return $event->reservations()->with('space:id,name,zone_class')->orderBy('start_at')->get();
    }

    /**
     * @param  array{space_id: string, start_at: string, end_at: string, status?: string}  $data
     */
    public function createForEvent(User $user, Event $event, array $data): Reservation
    {
        if (! $user->hasPermissionTo(Permissions::RESERVATIONS_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to manage reservations.');
        }

        OperationalAccess::ensureCanManageEvent($user, $event);

        if (! $this->scheduling->isAvailable($data['space_id'], CarbonImmutable::parse($data['start_at']), CarbonImmutable::parse($data['end_at']))) {
            throw new RuntimeException('The selected space is not available for that time window.');
        }

        $status = BookingStatus::tryFrom($data['status'] ?? BookingStatus::Tentative->value)
            ?? BookingStatus::Tentative;

        $reservation = Reservation::query()->create([
            'space_id' => $data['space_id'],
            'event_id' => $event->id,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'status' => $status->value,
        ]);

        $this->activityLog->record(
            ActivityLogAction::Created,
            'Space reservation created',
            $user,
            event: $event,
            properties: ['reservation_id' => $reservation->id, 'space_id' => $reservation->space_id],
        );

        return $reservation->load('space:id,name,zone_class');
    }

    public function delete(User $user, Reservation $reservation): void
    {
        if (! $user->hasPermissionTo(Permissions::RESERVATIONS_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to manage reservations.');
        }

        $event = $reservation->event;

        if ($event instanceof Event) {
            OperationalAccess::ensureCanManageEvent($user, $event);
        }

        $reservation->delete();

        if ($event instanceof Event) {
            $this->activityLog->record(
                ActivityLogAction::Updated,
                'Space reservation removed',
                $user,
                event: $event,
                properties: ['reservation_id' => $reservation->id],
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Reservation $reservation): array
    {
        return [
            'id' => $reservation->id,
            'space_id' => $reservation->space_id,
            'event_id' => $reservation->event_id,
            'start_at' => $reservation->start_at->toIso8601String(),
            'end_at' => $reservation->end_at->toIso8601String(),
            'status' => $reservation->status->value,
            'status_label' => $reservation->status->label(),
            'space' => $reservation->relationLoaded('space') ? $reservation->space?->only(['id', 'name', 'zone_class']) : null,
        ];
    }
}
