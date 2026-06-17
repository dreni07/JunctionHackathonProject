<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OperationalChangeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $model_type
 * @property string $model_id
 * @property string $action
 * @property string $summary
 * @property array<string, mixed>|null $payload
 * @property Carbon $occurred_at
 */
class OperationalChange extends Model
{
    /** @use HasFactory<OperationalChangeFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'summary',
        'payload',
        'occurred_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
