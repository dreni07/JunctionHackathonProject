<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Temporal bounds and AI enforcement protocol for a zone (Table 2.1).
 *
 * @property int $id
 * @property string $zone_classification
 * @property string $weekday_hours
 * @property string $weekend_hours
 * @property string $enforcement_protocol
 */
class ZoneOperatingRule extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'zone_classification',
        'weekday_hours',
        'weekend_hours',
        'enforcement_protocol',
    ];
}
