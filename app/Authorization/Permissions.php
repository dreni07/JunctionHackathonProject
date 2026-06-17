<?php

declare(strict_types=1);

namespace App\Authorization;

/**
 * Central catalog of every permission in the platform.
 *
 * Use these constants on routes (->can(Permissions::EVENTS_MANAGE)) and in
 * policies so a permission name is never a magic string that can be typo'd.
 */
final class Permissions
{
    // Event requests (the inbound inquiries)
    public const REQUESTS_CREATE = 'requests.create';

    public const REQUESTS_VIEW = 'requests.view';

    public const REQUESTS_MANAGE = 'requests.manage';

    // Events (the structured, planned events)
    public const EVENTS_VIEW = 'events.view';

    public const EVENTS_MANAGE = 'events.manage';

    // Spaces (Blue/Orange/Green/Yellow + transitional areas)
    public const SPACES_VIEW = 'spaces.view';

    public const SPACES_MANAGE = 'spaces.manage';

    // Inventory & operational assets
    public const INVENTORY_VIEW = 'inventory.view';

    public const INVENTORY_MANAGE = 'inventory.manage';

    // Space & asset reservations
    public const RESERVATIONS_VIEW = 'reservations.view';

    public const RESERVATIONS_MANAGE = 'reservations.manage';

    // Quotations / proposals
    public const QUOTATIONS_VIEW = 'quotations.view';

    public const QUOTATIONS_CREATE = 'quotations.create';

    public const QUOTATIONS_APPROVE = 'quotations.approve';

    // Setup / teardown tasks
    public const TASKS_VIEW = 'tasks.view';

    public const TASKS_MANAGE = 'tasks.manage';

    // Scheduling & resource conflicts
    public const CONFLICTS_VIEW = 'conflicts.view';

    // Audit trail of decisions, changes, approvals
    public const ACTIVITY_VIEW = 'activity.view';

    // User administration
    public const USERS_MANAGE = 'users.manage';

    // The AI copilot / agent
    public const COPILOT_USE = 'copilot.use';

    /**
     * Full catalog: name => [label, group]. Consumed by the seeder.
     *
     * @return array<string, array{label: string, group: string}>
     */
    public static function catalog(): array
    {
        return [
            self::REQUESTS_CREATE => ['label' => 'Submit event requests', 'group' => 'requests'],
            self::REQUESTS_VIEW => ['label' => 'View event requests', 'group' => 'requests'],
            self::REQUESTS_MANAGE => ['label' => 'Manage event requests', 'group' => 'requests'],

            self::EVENTS_VIEW => ['label' => 'View events', 'group' => 'events'],
            self::EVENTS_MANAGE => ['label' => 'Manage events', 'group' => 'events'],

            self::SPACES_VIEW => ['label' => 'View spaces', 'group' => 'spaces'],
            self::SPACES_MANAGE => ['label' => 'Manage spaces', 'group' => 'spaces'],

            self::INVENTORY_VIEW => ['label' => 'View inventory', 'group' => 'inventory'],
            self::INVENTORY_MANAGE => ['label' => 'Manage inventory', 'group' => 'inventory'],

            self::RESERVATIONS_VIEW => ['label' => 'View reservations', 'group' => 'reservations'],
            self::RESERVATIONS_MANAGE => ['label' => 'Manage reservations', 'group' => 'reservations'],

            self::QUOTATIONS_VIEW => ['label' => 'View quotations', 'group' => 'quotations'],
            self::QUOTATIONS_CREATE => ['label' => 'Create quotations', 'group' => 'quotations'],
            self::QUOTATIONS_APPROVE => ['label' => 'Approve quotations', 'group' => 'quotations'],

            self::TASKS_VIEW => ['label' => 'View tasks', 'group' => 'tasks'],
            self::TASKS_MANAGE => ['label' => 'Manage tasks', 'group' => 'tasks'],

            self::CONFLICTS_VIEW => ['label' => 'View conflicts', 'group' => 'conflicts'],

            self::ACTIVITY_VIEW => ['label' => 'View activity log', 'group' => 'activity'],

            self::USERS_MANAGE => ['label' => 'Manage users', 'group' => 'users'],

            self::COPILOT_USE => ['label' => 'Use the AI copilot', 'group' => 'copilot'],
        ];
    }
}
