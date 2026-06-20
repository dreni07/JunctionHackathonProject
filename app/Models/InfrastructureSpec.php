<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Infrastructure matrix & utility specification per room category (Table 3.1).
 *
 * @property int $id
 * @property string $room_category
 * @property string $av_assets
 * @property string $climate_support
 * @property string $ingress_routing
 * @property int $power_kw
 */
class InfrastructureSpec extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'room_category',
        'av_assets',
        'climate_support',
        'ingress_routing',
        'power_kw',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'power_kw' => 'integer',
        ];
    }
}
