<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Enums\OrganizationType;
use App\Models\Organization;
use App\Models\User;
use App\Support\OperationalAccess;
use InvalidArgumentException;

class OrganizationManagementService
{
    public function find(User $user, Organization $organization): Organization
    {
        OperationalAccess::ensureCanManageOrganization($user, $organization);

        return $organization->loadCount(['users', 'eventRequests', 'events']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, Organization $organization, array $data): Organization
    {
        OperationalAccess::ensureCanManageOrganization($user, $organization);

        $payload = [];

        if (array_key_exists('name', $data)) {
            $payload['name'] = $data['name'];
        }

        if (array_key_exists('type', $data)) {
            $type = OrganizationType::tryFrom((string) $data['type']);
            if (! $type instanceof OrganizationType) {
                throw new InvalidArgumentException('Invalid organization type.');
            }
            $payload['type'] = $type->value;
        }

        $organization->update($payload);

        return $organization->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Organization $organization): array
    {
        return [
            'id' => $organization->id,
            'name' => $organization->name,
            'type' => $organization->type?->value,
            'type_label' => $organization->type?->label(),
            'users_count' => $organization->users_count ?? null,
            'event_requests_count' => $organization->event_requests_count ?? null,
            'events_count' => $organization->events_count ?? null,
        ];
    }
}
