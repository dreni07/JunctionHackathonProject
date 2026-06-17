<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AlertSource;
use App\Enums\AlertStatus;
use App\Enums\RiskLevel;
use Database\Factories\AlertFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int|null $user_id
 * @property string|null $event_id
 * @property string|null $event_request_id
 * @property string|null $final_proposal_id
 * @property AlertSource $source
 * @property RiskLevel $severity
 * @property AlertStatus $status
 * @property string $title
 * @property string $message
 * @property string|null $agent_name
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $read_at
 * @property Carbon|null $dismissed_at
 */
class Alert extends Model
{
    /** @use HasFactory<AlertFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'event_id',
        'event_request_id',
        'final_proposal_id',
        'source',
        'severity',
        'status',
        'title',
        'message',
        'agent_name',
        'metadata',
        'read_at',
        'dismissed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => AlertSource::class,
            'severity' => RiskLevel::class,
            'status' => AlertStatus::class,
            'metadata' => 'array',
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Alert>  $query
     * @return Builder<Alert>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', AlertStatus::Unread);
    }

    public function markAsRead(): void
    {
        $this->update([
            'status' => AlertStatus::Read,
            'read_at' => now(),
        ]);
    }

    public function dismiss(): void
    {
        $this->update([
            'status' => AlertStatus::Dismissed,
            'dismissed_at' => now(),
        ]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<EventRequest, $this>
     */
    public function eventRequest(): BelongsTo
    {
        return $this->belongsTo(EventRequest::class);
    }

    /**
     * @return BelongsTo<FinalProposal, $this>
     */
    public function finalProposal(): BelongsTo
    {
        return $this->belongsTo(FinalProposal::class);
    }
}
