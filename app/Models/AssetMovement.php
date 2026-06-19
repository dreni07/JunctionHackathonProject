<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One entry in an asset's QR movement history.
 *
 * @property string $id
 * @property string $asset_id
 * @property string|null $from_location
 * @property string $to_location
 * @property string|null $event_id
 * @property int|null $moved_by
 * @property Carbon $moved_at
 */
class AssetMovement extends Model
{
    use HasUuids;

    protected $fillable = [
        'asset_id',
        'from_location',
        'to_location',
        'event_id',
        'moved_by',
        'moved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'moved_at' => 'datetime',
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

    /**
     * The staff member who moved the asset.
     *
     * @return BelongsTo<User, $this>
     */
    public function mover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}
