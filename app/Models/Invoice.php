<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $tenant_id
 * @property string|null $event_id
 * @property string|null $final_proposal_id
 * @property int|null $organization_id
 * @property string $reference
 * @property string $title
 * @property string $amount
 * @property string $amount_paid
 * @property InvoiceStatus $status
 * @property Carbon|null $issued_at
 * @property Carbon|null $due_at
 * @property string|null $notes
 */
class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'event_id',
        'final_proposal_id',
        'organization_id',
        'reference',
        'title',
        'amount',
        'amount_paid',
        'status',
        'issued_at',
        'due_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
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
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<FinalProposal, $this>
     */
    public function finalProposal(): BelongsTo
    {
        return $this->belongsTo(FinalProposal::class);
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->amount - (float) $this->amount_paid);
    }
}
