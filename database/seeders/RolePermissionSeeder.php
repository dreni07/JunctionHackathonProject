<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Authorization\Permissions;
use App\Enums\RoleName;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the roles, the full permission catalog, and the permissions
     * granted to each role.
     */
    public function run(): void
    {
        $this->seedPermissions();

        foreach (RoleName::cases() as $roleName) {
            $role = Role::query()->updateOrCreate(
                ['name' => $roleName->value],
                ['label' => $roleName->label(), 'description' => $roleName->description()],
            );

            $role->syncPermissionsByName($this->permissionsFor($roleName));
        }
    }

    /**
     * Create/refresh every permission from the central catalog.
     */
    private function seedPermissions(): void
    {
        foreach (Permissions::catalog() as $name => $meta) {
            Permission::query()->updateOrCreate(
                ['name' => $name],
                ['label' => $meta['label'], 'group' => $meta['group']],
            );
        }
    }

    /**
     * The permission names granted to a given role.
     *
     * @return list<string>
     */
    private function permissionsFor(RoleName $role): array
    {
        return match ($role) {
            // The external customer: submits and tracks only their own requests.
            RoleName::Organizer => [
                Permissions::REQUESTS_CREATE,
                Permissions::REQUESTS_VIEW,
                Permissions::EVENTS_VIEW,
                Permissions::QUOTATIONS_VIEW,
                Permissions::COPILOT_USE,
            ],

            // The operational team: runs the day-to-day event machinery,
            // but does not give final approval or manage users.
            RoleName::Operations => [
                Permissions::REQUESTS_VIEW,
                Permissions::REQUESTS_MANAGE,
                Permissions::EVENTS_VIEW,
                Permissions::EVENTS_MANAGE,
                Permissions::SPACES_VIEW,
                Permissions::INVENTORY_VIEW,
                Permissions::INVENTORY_MANAGE,
                Permissions::RESERVATIONS_VIEW,
                Permissions::RESERVATIONS_MANAGE,
                Permissions::QUOTATIONS_VIEW,
                Permissions::QUOTATIONS_CREATE,
                Permissions::TASKS_VIEW,
                Permissions::TASKS_MANAGE,
                Permissions::CONFLICTS_VIEW,
                Permissions::ACTIVITY_VIEW,
                Permissions::COPILOT_USE,
            ],

            // Management: everything operations can do, plus approvals,
            // space configuration, and user administration.
            RoleName::Management => array_merge(
                $this->permissionsFor(RoleName::Operations),
                [
                    Permissions::SPACES_MANAGE,
                    Permissions::QUOTATIONS_APPROVE,
                    Permissions::USERS_MANAGE,
                ],
            ),
        };
    }
}
