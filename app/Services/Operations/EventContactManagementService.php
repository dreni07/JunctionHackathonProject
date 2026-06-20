<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\EventContact;
use App\Models\EventRequest;
use App\Models\User;
use App\Support\OperationalAccess;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class EventContactManagementService
{
    public function listForRequest(User $user, EventRequest $eventRequest): Collection
    {
        OperationalAccess::ensureCanViewEventRequest($user, $eventRequest);

        return $eventRequest->contacts()->orderByDesc('is_primary')->orderBy('name')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, EventRequest $eventRequest, array $data): EventContact
    {
        OperationalAccess::ensureCanViewEventRequest($user, $eventRequest);

        if ($data['is_primary'] ?? false) {
            $eventRequest->contacts()->update(['is_primary' => false]);
        }

        return EventContact::query()->create([
            'event_request_id' => $eventRequest->id,
            'organization_id' => $eventRequest->organization_id,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? 'contact',
            'is_primary' => (bool) ($data['is_primary'] ?? false),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, EventContact $contact, array $data): EventContact
    {
        $eventRequest = $contact->eventRequest;

        if (! $eventRequest instanceof EventRequest) {
            throw new InvalidArgumentException('Contact is not linked to an event request.');
        }

        OperationalAccess::ensureCanViewEventRequest($user, $eventRequest);

        if ($data['is_primary'] ?? false) {
            $eventRequest->contacts()->whereKeyNot($contact->id)->update(['is_primary' => false]);
        }

        $contact->update(collect($data)->only(['name', 'email', 'phone', 'role', 'is_primary'])->all());

        return $contact->fresh();
    }

    public function delete(User $user, EventContact $contact): void
    {
        $eventRequest = $contact->eventRequest;

        if (! $eventRequest instanceof EventRequest) {
            throw new InvalidArgumentException('Contact is not linked to an event request.');
        }

        OperationalAccess::ensureCanViewEventRequest($user, $eventRequest);
        $contact->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(EventContact $contact): array
    {
        return [
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'role' => $contact->role,
            'is_primary' => $contact->is_primary,
        ];
    }
}
