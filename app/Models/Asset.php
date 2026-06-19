<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A physical asset tracked via QR code.
 *
 * @property string $id
 * @property string $name
 * @property AssetType $type
 * @property string $qr_code
 * @property AssetStatus $status
 * @property string|null $current_location
 * @property string|null $assigned_event_id
 */
class Asset extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'type',
        'qr_code',
        'status',
        'current_location',
        'assigned_event_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AssetType::class,
            'status' => AssetStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function assignedEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'assigned_event_id');
    }

    /**
     * @return HasMany<AssetMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(AssetMovement::class);
    }

    /**
     * @return HasMany<AssetReservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(AssetReservation::class);
    }
}
