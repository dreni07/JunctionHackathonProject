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
 * @property string $name
 * @property int $floor
 * @property int $capacity
 * @property SpaceType $type
 * @property array<string, mixed>|null $features
 * @property array<string, mixed>|null $location_geometry
 */
class Space extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'floor',
        'capacity',
        'type',
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
            'features' => 'array',
            'location_geometry' => 'array',
        ];
    }

    /**
     * Availability / booking slots for this space.
     *
     * @return HasMany<SpaceAvailability, $this>
     */
    public function availability(): HasMany
    {
        return $this->hasMany(SpaceAvailability::class);
    }
}
