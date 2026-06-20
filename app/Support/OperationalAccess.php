<?php

declare(strict_types=1);

namespace App\Support;

use App\Authorization\Permissions;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\FinalProposal;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;

/**
 * Scopes and authorization helpers for operational CRUD endpoints.
 */
final class OperationalAccess
{
    public static function managesRequests(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::REQUESTS_MANAGE);
    }

    public static function managesEvents(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::EVENTS_MANAGE);
    }

    /**
     * @param  Builder<EventRequest>  $query
     * @return Builder<EventRequest>
     */
    public static function scopeEventRequests(Builder $query, User $user): Builder
    {
        if (self::managesRequests($user)) {
            return $query;
        }

        return $query->where(function (Builder $scoped) use ($user): void {
            $scoped->where('submitted_by', $user->id);

            if ($user->organization_id !== null) {
                $scoped->orWhere('organization_id', $user->organization_id);
            }
        });
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public static function scopeEvents(Builder $query, User $user): Builder
    {
        if (self::managesEvents($user)) {
            return $query;
        }

        if (! $user->hasPermissionTo(Permissions::EVENTS_VIEW)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $scoped) use ($user): void {
            $scoped->where('created_by', $user->id);

            if ($user->organization_id !== null) {
                $scoped->orWhere('organization_id', $user->organization_id);
            }
        });
    }

    /**
     * @param  Builder<FinalProposal>  $query
     * @return Builder<FinalProposal>
     */
    public static function scopeProposals(Builder $query, User $user): Builder
    {
        if ($user->hasPermissionTo(Permissions::QUOTATIONS_CREATE)
            || $user->hasPermissionTo(Permissions::QUOTATIONS_APPROVE)) {
            return $query;
        }

        if (! $user->hasPermissionTo(Permissions::QUOTATIONS_VIEW)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $scoped) use ($user): void {
            $scoped->where('created_by', $user->id);

            if ($user->organization_id !== null) {
                $scoped->orWhere('organization_id', $user->organization_id);
            }
        });
    }

    public static function ensureCanViewEventRequest(User $user, EventRequest $eventRequest): void
    {
        if (self::managesRequests($user)) {
            return;
        }

        if ($eventRequest->submitted_by === $user->id) {
            return;
        }

        if ($user->organization_id !== null && $eventRequest->organization_id === $user->organization_id) {
            return;
        }

        throw new AuthorizationException('You are not allowed to view this event request.');
    }

    public static function ensureCanManageEventRequest(User $user, EventRequest $eventRequest): void
    {
        if (! self::managesRequests($user)) {
            throw new AuthorizationException('You are not allowed to manage event requests.');
        }
    }

    public static function ensureCanViewEvent(User $user, Event $event): void
    {
        if (self::managesEvents($user)) {
            return;
        }

        if ($event->created_by === $user->id) {
            return;
        }

        if ($user->organization_id !== null && $event->organization_id === $user->organization_id) {
            return;
        }

        throw new AuthorizationException('You are not allowed to view this event.');
    }

    public static function ensureCanManageEvent(User $user, Event $event): void
    {
        if (! self::managesEvents($user)) {
            throw new AuthorizationException('You are not allowed to manage events.');
        }
    }

    public static function ensureCanViewProposal(User $user, FinalProposal $proposal): void
    {
        if ($user->hasPermissionTo(Permissions::QUOTATIONS_CREATE)
            || $user->hasPermissionTo(Permissions::QUOTATIONS_APPROVE)) {
            return;
        }

        if ($proposal->created_by === $user->id) {
            return;
        }

        if ($user->organization_id !== null && $proposal->organization_id === $user->organization_id) {
            return;
        }

        if ($user->hasPermissionTo(Permissions::QUOTATIONS_VIEW)) {
            return;
        }

        throw new AuthorizationException('You are not allowed to view this proposal.');
    }

    public static function ensureCanManageOrganization(User $user, Organization $organization): void
    {
        if ($user->hasPermissionTo(Permissions::USERS_MANAGE)) {
            return;
        }

        if ($user->organization_id === $organization->id) {
            return;
        }

        throw new AuthorizationException('You are not allowed to manage this organization.');
    }
}
