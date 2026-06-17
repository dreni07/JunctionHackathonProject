<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A space's booking slot. A non-null event_id means the slot is taken —
 * this table is what the conflict engine reads.
 *
 * @property string $id
 * @property string $space_id
 * @property Carbon $date
 * @property string $start_time
 * @property string $end_time
 * @property string|null $event_id
 */
class SpaceAvailability extends Model
{
    use HasUuids;

    protected $table = 'space_availability';

    protected $fillable = [
        'space_id',
        'date',
        'start_time',
        'end_time',
        'event_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Space, $this>
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
