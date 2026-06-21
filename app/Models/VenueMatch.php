<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One venue's confidence score and calendar availability for an event request
 * (the matching agent's output, persisted).
 *
 * @property string $id
 * @property string $event_request_id
 * @property string $space_id
 * @property string $confidence_score
 * @property int $rank
 * @property bool $available
 * @property bool $selected
 */
class VenueMatch extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_request_id',
        'space_id',
        'confidence_score',
        'rank',
        'available',
        'selected',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'confidence_score' => 'decimal:2',
            'rank' => 'integer',
            'available' => 'boolean',
            'selected' => 'boolean',
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
     * @return BelongsTo<Space, $this>
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }
}
