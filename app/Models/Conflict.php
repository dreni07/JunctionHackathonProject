<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConflictStatus;
use App\Enums\ConflictType;
use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $event_id
 * @property string|null $event_request_id
 * @property string|null $final_proposal_id
 * @property string|null $reservation_id
 * @property string|null $space_id
 * @property ConflictType $type
 * @property ConflictStatus $status
 * @property RiskLevel $severity
 * @property string $title
 * @property string|null $description
 * @property array<string, mixed>|null $metadata
 * @property Carbon $detected_at
 * @property Carbon|null $resolved_at
 * @property int|null $resolved_by
 */
class Conflict extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_id',
        'event_request_id',
        'final_proposal_id',
        'reservation_id',
        'space_id',
        'type',
        'status',
        'severity',
        'title',
        'description',
        'metadata',
        'detected_at',
        'resolved_at',
        'resolved_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ConflictType::class,
            'status' => ConflictStatus::class,
            'severity' => RiskLevel::class,
            'metadata' => 'array',
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
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
     * @return BelongsTo<FinalProposal, $this>
     */
    public function finalProposal(): BelongsTo
    {
        return $this->belongsTo(FinalProposal::class);
    }

    /**
     * @return BelongsTo<Reservation, $this>
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * @return BelongsTo<Space, $this>
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
