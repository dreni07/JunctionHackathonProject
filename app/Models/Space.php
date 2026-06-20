<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SpaceType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A Pyramid venue (Blue/Orange/Green/Yellow halls + transitional areas).
 *
 * @property string $id
 * @property string|null $room_code
 * @property string|null $box_ref
 * @property string|null $zone_class
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
     * Calendar reservations (bookings) for this space.
     *
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
