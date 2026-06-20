<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Aggregated structural statistics for one floor level (Table 1.3).
 *
 * @property int $id
 * @property int $level
 * @property string $label
 * @property int $active_boxes
 * @property int $box_footprint_sqm
 * @property int $tumo_nodes
 * @property int $public_nodes
 * @property int $max_human_load
 */
class BuildingLevel extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'level',
        'label',
        'active_boxes',
        'box_footprint_sqm',
        'tumo_nodes',
        'public_nodes',
        'max_human_load',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'active_boxes' => 'integer',
            'box_footprint_sqm' => 'integer',
            'tumo_nodes' => 'integer',
            'public_nodes' => 'integer',
            'max_human_load' => 'integer',
        ];
    }
}
