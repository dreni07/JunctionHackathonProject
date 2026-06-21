<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApprovalDecision;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $approvable_type
 * @property string $approvable_id
 * @property int $requested_by
 * @property int|null $decided_by
 * @property ApprovalDecision $decision
 * @property string|null $notes
 * @property Carbon|null $decided_at
 */
class Approval extends Model
{
    use HasUuids;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'requested_by',
        'decided_by',
        'decision',
        'notes',
        'decided_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'decision' => ApprovalDecision::class,
            'decided_at' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
