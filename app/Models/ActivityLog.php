<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityLogAction;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string|null $event_id
 * @property string|null $event_request_id
 * @property string|null $final_proposal_id
 * @property int|null $user_id
 * @property ActivityLogAction $action
 * @property string $description
 * @property array<string, mixed>|null $properties
 */
class ActivityLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_id',
        'event_request_id',
        'final_proposal_id',
        'user_id',
        'action',
        'description',
        'properties',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => ActivityLogAction::class,
            'properties' => 'array',
        ];
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
