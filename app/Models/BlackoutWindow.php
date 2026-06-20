<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Security/maintenance blackout interval when no events may be scheduled
 * (Section 2.4).
 *
 * @property int $id
 * @property string $scope
 * @property string $days
 * @property string $start_time
 * @property string $end_time
 * @property string $reason
 */
class BlackoutWindow extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'scope',
        'days',
        'start_time',
        'end_time',
        'reason',
    ];
}
