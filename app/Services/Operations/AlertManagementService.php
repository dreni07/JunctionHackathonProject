<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Authorization\Permissions;
use App\Enums\AlertCategory;
use App\Enums\AlertSource;
use App\Enums\AlertStatus;
use App\Enums\RiskLevel;
use App\Models\Alert;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class AlertManagementService
{
    /** @var list<string> */
    private const EAGER = ['raisedBy:id,name,worker_role', 'spaces:id,name,zone_class,floor'];

    /**
     * @return LengthAwarePaginator<int, Alert>
     */
    public function paginate(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Alert::query()->with(self::EAGER)->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['mine'])) {
            $query->where('raised_by', $user->id);
        }

        if (! $user->hasPermissionTo(Permissions::REQUESTS_MANAGE)) {
            $query->where(function ($scoped) use ($user): void {
                $scoped->where('user_id', $user->id)
                    ->orWhereNull('user_id')
                    ->orWhere('raised_by', $user->id);
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Raise a new alert on behalf of a worker, optionally tied to one or more
     * venues (the "related entities").
     *
     * @param  array{title: string, message: string, severity?: string, category?: string|null, event_id?: string|null, space_ids?: list<string>}  $data
     */
    public function create(User $user, array $data): Alert
    {
        $severity = RiskLevel::tryFrom((string) ($data['severity'] ?? RiskLevel::Medium->value)) ?? RiskLevel::Medium;
        $category = isset($data['category']) ? AlertCategory::tryFrom((string) $data['category']) : null;

        $alert = Alert::query()->create([
            'raised_by' => $user->id,
            'source' => AlertSource::Worker->value,
            'category' => $category?->value,
            'severity' => $severity->value,
            'status' => AlertStatus::Unread->value,
            'title' => $data['title'],
            'message' => $data['message'],
            'event_id' => $data['event_id'] ?? null,
        ]);

        if (! empty($data['space_ids'])) {
            $alert->spaces()->sync($data['space_ids']);
        }

        return $alert->load(self::EAGER);
    }

    public function markAsRead(User $user, Alert $alert): Alert
    {
        $this->ensureCanManage($user, $alert);
        $alert->markAsRead();

        return $alert->fresh(self::EAGER);
    }

    public function dismiss(User $user, Alert $alert): Alert
    {
        $this->ensureCanManage($user, $alert);
        $alert->dismiss();

        return $alert->fresh(self::EAGER);
    }

    public function resolve(User $user, Alert $alert): Alert
    {
        $this->ensureCanManage($user, $alert);
        $alert->resolve();

        return $alert->fresh(self::EAGER);
    }

    private function ensureCanManage(User $user, Alert $alert): void
    {
        $owns = $alert->user_id === $user->id || $alert->raised_by === $user->id;

        if (! $owns && ! $user->hasPermissionTo(Permissions::REQUESTS_MANAGE)) {
            throw new InvalidArgumentException('You are not allowed to update this alert.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Alert $alert): array
    {
        return [
            'id' => $alert->id,
            'title' => $alert->title,
            'message' => $alert->message,
            'source' => $alert->source->value,
            'source_label' => $alert->source->label(),
            'category' => $alert->category?->value,
            'category_label' => $alert->category?->label(),
            'severity' => $alert->severity->value,
            'severity_label' => $alert->severity->label(),
            'status' => $alert->status->value,
            'status_label' => $alert->status->label(),
            'agent_name' => $alert->agent_name,
            'event_id' => $alert->event_id,
            'event_request_id' => $alert->event_request_id,
            'raised_by' => $alert->relationLoaded('raisedBy')
                ? $alert->raisedBy?->only(['id', 'name', 'worker_role'])
                : null,
            'spaces' => $alert->relationLoaded('spaces')
                ? $alert->spaces->map(fn ($space): array => $space->only(['id', 'name', 'zone_class', 'floor']))->all()
                : [],
            'read_at' => $alert->read_at?->toIso8601String(),
            'resolved_at' => $alert->resolved_at?->toIso8601String(),
            'created_at' => $alert->created_at?->toIso8601String(),
        ];
    }
}
