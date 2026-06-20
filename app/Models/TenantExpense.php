<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExpenseCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $tenant_id
 * @property ExpenseCategory $category
 * @property string $title
 * @property string $amount
 * @property Carbon $incurred_at
 * @property string|null $notes
 * @property int|null $recorded_by
 */
class TenantExpense extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'category',
        'title',
        'amount',
        'incurred_at',
        'notes',
        'recorded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ExpenseCategory::class,
            'amount' => 'decimal:2',
            'incurred_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
