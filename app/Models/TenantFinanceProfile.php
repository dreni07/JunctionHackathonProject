<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $annual_budget
 * @property string $operating_reserve
 * @property string $currency
 * @property string|null $notes
 */
class TenantFinanceProfile extends Model
{
    protected $fillable = [
        'tenant_id',
        'annual_budget',
        'operating_reserve',
        'currency',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'annual_budget' => 'decimal:2',
            'operating_reserve' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
