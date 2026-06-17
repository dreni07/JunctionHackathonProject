<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One space-time booking on the calendar — the conflict source of truth.
 *
 * @property string $id
 * @property string $space_id
 * @property string|null $event_id
 * @property string|null $final_proposal_id
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property BookingStatus $status
 */
class Reservation extends Model
{
    use HasUuids;

    protected $fillable = [
        'space_id',
        'event_id',
        'final_proposal_id',
        'start_at',
        'end_at',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'status' => BookingStatus::class,
        ];
    }

    /**
     * Only reservations that actually occupy a slot (tentative or confirmed).
     *
     * @param  Builder<Reservation>  $query
     * @return Builder<Reservation>
     */
    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', BookingStatus::blocking());
    }

    /**
     * Reservations whose window overlaps [$start, $end). The whole point of
     * this table: the conflict agent calls
     *   Reservation::where('space_id', $id)->blocking()->overlapping($s, $e)->exists()
     *
     * @param  Builder<Reservation>  $query
     * @return Builder<Reservation>
     */
    public function scopeOverlapping(Builder $query, string $start, string $end): Builder
    {
        return $query->where('start_at', '<', $end)->where('end_at', '>', $start);
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

    /**
     * @return BelongsTo<FinalProposal, $this>
     */
    public function finalProposal(): BelongsTo
    {
        return $this->belongsTo(FinalProposal::class);
    }
}
