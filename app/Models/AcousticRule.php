<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Acoustic proximity restriction & buffer rule (Table 2.2).
 *
 * @property int $id
 * @property string $event_target_profile
 * @property string $collision_profile
 * @property string $buffer_requirement
 */
class AcousticRule extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'event_target_profile',
        'collision_profile',
        'buffer_requirement',
    ];
}
