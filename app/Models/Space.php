<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SpaceType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A Pyramid venue (Blue/Orange/Green/Yellow halls + transitional areas).
 *
 * @property string $id
 * @property string|null $room_code
 * @property string|null $box_ref
 * @property string|null $zone_class
 * @property int|null $tenant_id
 * @property string $name
 * @property int $floor
 * @property int $capacity
 * @property SpaceType $type
 * @property string|null $functional_type
 * @property int|null $area_sqm
 * @property string|null $workload_target
 * @property array<string, mixed>|null $features
 * @property array<string, mixed>|null $location_geometry
 */
class Space extends Model
{
    use HasUuids;

    protected $fillable = [
        'room_code',
        'box_ref',
        'zone_class',
        'tenant_id',
        'name',
        'floor',
        'capacity',
        'type',
        'functional_type',
        'area_sqm',
        'workload_target',
        'features',
        'location_geometry',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'capacity' => 'integer',
            'type' => SpaceType::class,
            'area_sqm' => 'integer',
            'features' => 'array',
            'location_geometry' => 'array',
        ];
    }

    /**
     * The Pyramid branch that operates this venue.
     *
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Calendar reservations (bookings) for this space.
     *
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
