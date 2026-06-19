<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventType;
use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * A proposal made to the Pyramid. Accepting it creates the Event.
 *
 * @property string $id
 * @property string|null $organization_id
 * @property int|null $created_by
 * @property string|null $proposed_space_id
 * @property int|null $proposed_capacity
 * @property string|null $proposed_price
 * @property string|null $title
 * @property EventType|null $event_type
 * @property Carbon|null $start_at
 * @property Carbon|null $end_at
 * @property string|null $description
 * @property array<string, mixed>|null $event_data
 * @property ProposalStatus $status
 * @property string|null $event_id
 */
class FinalProposal extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'event_request_id',
        'created_by',
        'proposed_space_id',
        'proposed_capacity',
        'proposed_price',
        'title',
        'event_type',
        'start_at',
        'end_at',
        'description',
        'event_data',
        'status',
        'event_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'proposed_capacity' => 'integer',
            'proposed_price' => 'decimal:2',
            'event_type' => EventType::class,
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'event_data' => 'array',
            'status' => ProposalStatus::class,
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
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<Space, $this>
     */
    public function proposedSpace(): BelongsTo
    {
        return $this->belongsTo(Space::class, 'proposed_space_id');
    }

    /**
     * The event created once this proposal is accepted (null beforehand).
     *
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<EventRequest, $this>
     */
    public function eventRequest(): BelongsTo
    {
        return $this->belongsTo(EventRequest::class);
    }

    /**
     * @return HasMany<QuotationLineItem, $this>
     */
    public function lineItems(): HasMany
    {
        return $this->hasMany(QuotationLineItem::class)->orderBy('sort_order');
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
