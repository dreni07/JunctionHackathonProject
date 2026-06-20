<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A recorded accept / reject decision on an event request — the dataset the
 * booking agent learns from.
 *
 * @property string $id
 * @property string $event_request_id
 * @property string|null $event_id
 * @property int|null $decided_by
 * @property string|null $matched_space_id
 * @property string $decision
 * @property string|null $rejection_reason
 * @property string|null $notes
 * @property string|null $event_type
 * @property int|null $attendees
 * @property string|null $price_suggested
 * @property string|null $price_agreed
 * @property array<string, mixed>|null $features
 */
class EventDecision extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_request_id',
        'event_id',
        'decided_by',
        'matched_space_id',
        'decision',
        'rejection_reason',
        'notes',
        'event_type',
        'attendees',
        'price_suggested',
        'price_agreed',
        'features',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendees' => 'integer',
            'price_suggested' => 'decimal:2',
            'price_agreed' => 'decimal:2',
            'features' => 'array',
        ];
    }

    /**
     * @return BelongsTo<EventRequest, $this>
     */
    public function eventRequest(): BelongsTo
    {
        return $this->belongsTo(EventRequest::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
