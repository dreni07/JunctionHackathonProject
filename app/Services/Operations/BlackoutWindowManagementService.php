<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Authorization\Permissions;
use App\Models\BlackoutWindow;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class BlackoutWindowManagementService
{
    /**
     * @return Collection<int, BlackoutWindow>
     */
    public function list(User $user): Collection
    {
        if (! $user->hasPermissionTo(Permissions::SPACES_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to view blackout windows.');
        }

        return BlackoutWindow::query()->orderBy('scope')->orderBy('start_time')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): BlackoutWindow
    {
        if (! $user->hasPermissionTo(Permissions::SPACES_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to create blackout windows.');
        }

        return BlackoutWindow::query()->create([
            'scope' => $data['scope'],
            'days' => $data['days'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'reason' => $data['reason'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, BlackoutWindow $blackoutWindow, array $data): BlackoutWindow
    {
        if (! $user->hasPermissionTo(Permissions::SPACES_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to update blackout windows.');
        }

        $blackoutWindow->update(collect($data)->only([
            'scope', 'days', 'start_time', 'end_time', 'reason',
        ])->all());

        return $blackoutWindow->fresh();
    }

    public function delete(User $user, BlackoutWindow $blackoutWindow): void
    {
        if (! $user->hasPermissionTo(Permissions::SPACES_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to delete blackout windows.');
        }

        $blackoutWindow->delete();
    }
}
