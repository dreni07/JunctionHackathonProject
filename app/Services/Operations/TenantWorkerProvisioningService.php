<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Enums\TaskState;
use App\Models\User;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class TenantWorkerProvisioningService
{
    /**
     * @return Collection<int, User>
     */
    public function listForManager(User $manager): Collection
    {
        $this->assertManager($manager);

        return User::query()
            ->where('tenant_id', $manager->tenant_id)
            ->where('account_type', AccountType::Operational)
            ->withCount([
                'tasks as open_tasks_count' => fn ($query) => $query->whereNotIn(
                    'state',
                    [TaskState::Finished->value, TaskState::Cancelled->value],
                ),
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array{name: string, email: string, password: string, worker_role: string}  $data
     */
    public function create(User $manager, array $data): User
    {
        $this->assertManager($manager);

        $tenant = $manager->tenant;

        if ($tenant === null) {
            throw new InvalidArgumentException('Manager has no tenant.');
        }

        $assignableRoles = $tenant->assignableWorkerRoles();

        if (! in_array($data['worker_role'], $assignableRoles, true)) {
            throw new InvalidArgumentException('Invalid worker role for this tenant.');
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'account_type' => AccountType::Operational,
            'tenant_id' => $tenant->id,
            'worker_role' => $data['worker_role'],
            'email_verified_at' => now(),
        ]);

        $user->assignRole(RoleName::Operations);

        return $user;
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     worker_role: string|null,
     *     created_at: string|null
     * }
     */
    public function serializeWorker(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'worker_role' => $user->worker_role,
            'open_tasks_count' => (int) ($user->open_tasks_count ?? 0),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    private function assertManager(User $manager): void
    {
        if (! $manager->isTenantManager()) {
            throw new InvalidArgumentException('Only tenant managers can manage workers.');
        }
    }
}
