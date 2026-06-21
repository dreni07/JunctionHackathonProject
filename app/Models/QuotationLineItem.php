<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuotationLineCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $final_proposal_id
 * @property string $description
 * @property QuotationLineCategory $category
 * @property int $quantity
 * @property string $unit_price
 * @property string $total
 * @property int $sort_order
 */
class QuotationLineItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'final_proposal_id',
        'description',
        'category',
        'quantity',
        'unit_price',
        'total',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => QuotationLineCategory::class,
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<FinalProposal, $this>
     */
    public function finalProposal(): BelongsTo
    {
        return $this->belongsTo(FinalProposal::class);
    }
}
