<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\EventType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * The single source of truth for an event.
 *
 * @property string $id
 * @property string|null $title
 * @property string|null $description
 * @property EventStatus $status
 * @property EventType|null $event_type
 * @property int|null $attendees
 * @property Carbon|null $start_time
 * @property Carbon|null $end_time
 * @property string|null $budget
 * @property string|null $organization_id
 * @property int $created_by
 */
class Event extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'description',
        'status',
        'event_type',
        'attendees',
        'start_time',
        'end_time',
        'budget',
        'organization_id',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EventStatus::class,
            'event_type' => EventType::class,
            'attendees' => 'integer',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'budget' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The user (organizer/staff) who created the event.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasOne<EventRequirement, $this>
     */
    public function requirements(): HasOne
    {
        return $this->hasOne(EventRequirement::class);
    }

    /**
     * @return HasOne<EventState, $this>
     */
    public function state(): HasOne
    {
        return $this->hasOne(EventState::class);
    }

    /**
     * @return HasMany<SpaceAvailability, $this>
     */
    public function spaceBookings(): HasMany
    {
        return $this->hasMany(SpaceAvailability::class);
    }

    /**
     * @return HasMany<AssetReservation, $this>
     */
    public function assetReservations(): HasMany
    {
        return $this->hasMany(AssetReservation::class);
    }

    /**
     * Assets currently assigned to this event.
     *
     * @return HasMany<Asset, $this>
     */
    public function assignedAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'assigned_event_id');
    }
}
