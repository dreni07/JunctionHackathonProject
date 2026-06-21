<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Building-wide reference parameters for the Pyramid of Tirana (appendix header
 * + Table 1.3 totals).
 *
 * @property int $id
 * @property string $name
 * @property int $total_footprint_sqm
 * @property string $height_m
 * @property int $levels
 * @property string $access_points
 * @property string $allocation_rule
 * @property int $active_box_area_sqm
 * @property int $total_boxes
 * @property int $tumo_nodes
 * @property int $public_nodes
 * @property int $max_human_load
 * @property string|null $reference_baseline
 * @property string|null $source
 */
class FacilityProfile extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'total_footprint_sqm',
        'height_m',
        'levels',
        'access_points',
        'allocation_rule',
        'active_box_area_sqm',
        'total_boxes',
        'tumo_nodes',
        'public_nodes',
        'max_human_load',
        'reference_baseline',
        'source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_footprint_sqm' => 'integer',
            'height_m' => 'decimal:2',
            'levels' => 'integer',
            'active_box_area_sqm' => 'integer',
            'total_boxes' => 'integer',
            'tumo_nodes' => 'integer',
            'public_nodes' => 'integer',
            'max_human_load' => 'integer',
        ];
    }
}
