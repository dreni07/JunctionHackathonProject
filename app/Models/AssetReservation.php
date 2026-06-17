<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reserves a quantity of an asset for an event.
 *
 * @property string $id
 * @property string $asset_id
 * @property string $event_id
 * @property int $reserved_quantity
 * @property ReservationStatus $status
 */
class AssetReservation extends Model
{
    use HasUuids;

    protected $fillable = [
        'asset_id',
        'event_id',
        'reserved_quantity',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reserved_quantity' => 'integer',
            'status' => ReservationStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Asset, $this>
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
