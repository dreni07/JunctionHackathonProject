<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventRequestStatus;
use App\Enums\EventType;
use Database\Factories\EventRequestFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $organization_id
 * @property int $submitted_by
 * @property string|null $title
 * @property string|null $description
 * @property EventType|null $event_type
 * @property int|null $attendees
 * @property Carbon|null $preferred_start_at
 * @property Carbon|null $preferred_end_at
 * @property string|null $raw_intake
 * @property EventRequestStatus $status
 * @property string|null $final_proposal_id
 * @property string|null $event_id
 */
class EventRequest extends Model
{
    /** @use HasFactory<EventRequestFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'submitted_by',
        'title',
        'description',
        'event_type',
        'attendees',
        'matched_space_id',
        'price_suggested',
        'price_agreed',
        'price_per_sqm',
        'preferred_start_at',
        'preferred_end_at',
        'raw_intake',
        'status',
        'final_proposal_id',
        'event_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_type' => EventType::class,
            'attendees' => 'integer',
            'price_suggested' => 'decimal:2',
            'price_agreed' => 'decimal:2',
            'price_per_sqm' => 'decimal:2',
            'preferred_start_at' => 'datetime',
            'preferred_end_at' => 'datetime',
            'status' => EventRequestStatus::class,
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
     * @return BelongsTo<User, $this>
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * @return BelongsTo<FinalProposal, $this>
     */
    public function finalProposal(): BelongsTo
    {
        return $this->belongsTo(FinalProposal::class);
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The venue the matching + scheduling agents settled on.
     *
     * @return BelongsTo<Space, $this>
     */
    public function matchedSpace(): BelongsTo
    {
        return $this->belongsTo(Space::class, 'matched_space_id');
    }

    /**
     * Every candidate venue's confidence score for this request.
     *
     * @return HasMany<VenueMatch, $this>
     */
    public function venueMatches(): HasMany
    {
        return $this->hasMany(VenueMatch::class);
    }

    /**
     * @return HasMany<EventContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(EventContact::class);
    }

    /**
     * @return HasMany<Conflict, $this>
     */
    public function conflicts(): HasMany
    {
        return $this->hasMany(Conflict::class);
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * @return MorphMany<Approval, $this>
     */
    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    /**
     * @return HasMany<Alert, $this>
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
