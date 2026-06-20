<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VenueUnavailabilityType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A spell during which a venue is unavailable — blocked or out of service.
 *
 * @property string $id
 * @property string $space_id
 * @property VenueUnavailabilityType $type
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property string|null $reason
 * @property int|null $created_by
 */
class VenueUnavailability extends Model
{
    use HasUuids;

    protected $fillable = [
        'space_id',
        'type',
        'starts_at',
        'ends_at',
        'reason',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => VenueUnavailabilityType::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
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
     * In force at a given moment (defaults to now).
     *
     * @param  Builder<VenueUnavailability>  $query
     * @return Builder<VenueUnavailability>
     */
    public function scopeActiveAt(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $at ??= now();

        return $query
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $at));
    }

    /**
     * Overlapping a window [start, end] — used to vet an event's slot.
     *
     * @param  Builder<VenueUnavailability>  $query
     * @return Builder<VenueUnavailability>
     */
    public function scopeOverlapping(Builder $query, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $query
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $start))
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $end));
    }

    /**
     * A short human description of the spell, e.g. "Out of service until 5 Aug".
     */
    public function describe(): string
    {
        $label = $this->type->label();

        if ($this->starts_at === null && $this->ends_at === null) {
            return $label.' (indefinitely)';
        }

        if ($this->ends_at !== null) {
            return $label.' until '.$this->ends_at->isoFormat('D MMM YYYY');
        }

        return $label.' from '.$this->starts_at?->isoFormat('D MMM YYYY');
    }
}
