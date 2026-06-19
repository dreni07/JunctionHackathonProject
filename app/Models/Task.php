<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskPhase;
use App\Enums\TaskState;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * An operational task for an event, assigned to a Pyramid worker.
 *
 * @property string $id
 * @property string $event_id
 * @property string|null $organization_id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $description
 * @property TaskState $state
 * @property TaskPhase $phase
 * @property Carbon|null $due_at
 */
class Task extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_id',
        'organization_id',
        'user_id',
        'name',
        'description',
        'state',
        'phase',
        'due_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => TaskState::class,
            'phase' => TaskPhase::class,
            'due_at' => 'datetime',
        ];
    }

    /**
     * Assign this task to a Pyramid operational worker. Tasks may ONLY be
     * assigned to users in the operations role — this guard enforces it.
     *
     * @throws InvalidArgumentException
     */
    public function assignTo(User $worker): void
    {
        if (! $worker->isOperationalWorker()) {
            throw new InvalidArgumentException(
                'Tasks can only be assigned to Pyramid operational workers.',
            );
        }

        $this->user_id = $worker->id;
        $this->save();
    }

    /**
     * The event this task belongs to.
     *
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The assigned operational worker.
     *
     * @return BelongsTo<User, $this>
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
