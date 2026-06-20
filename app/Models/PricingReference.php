<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One past (or agent-suggested) event and the price it was set at — the pricing
 * agent's training data.
 *
 * @property int $id
 * @property string $source
 * @property string|null $organizer
 * @property string $event_type
 * @property string|null $venue_name
 * @property string|null $floor
 * @property int $area_sqm
 * @property int $duration_days
 * @property int $attendees
 * @property string $price_eur
 * @property string $price_per_sqm
 * @property string|null $notes
 * @property string|null $event_request_id
 */
class PricingReference extends Model
{
    protected $fillable = [
        'source',
        'organizer',
        'event_type',
        'venue_name',
        'floor',
        'area_sqm',
        'duration_days',
        'attendees',
        'price_eur',
        'price_per_sqm',
        'notes',
        'event_request_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'area_sqm' => 'integer',
            'duration_days' => 'integer',
            'attendees' => 'integer',
            'price_eur' => 'decimal:2',
            'price_per_sqm' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<EventRequest, $this>
     */
    public function eventRequest(): BelongsTo
    {
        return $this->belongsTo(EventRequest::class);
    }
}
