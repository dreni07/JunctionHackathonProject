<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Human occupancy density & structural safety standard (Table 1.1).
 *
 * @property int $id
 * @property string $functional_category
 * @property string $area_metric_sqm
 * @property string $allocation_rule
 */
class OccupancyStandard extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'functional_category',
        'area_metric_sqm',
        'allocation_rule',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'area_metric_sqm' => 'decimal:2',
        ];
    }
}
