<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AgentStage;
use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Agent brain snapshot — how far the AI has progressed planning an event.
 *
 * @property string $id
 * @property string $event_id
 * @property AgentStage $stage
 * @property int $completion_score
 * @property array<int, string>|null $missing_fields
 * @property RiskLevel|null $risk_level
 */
class EventState extends Model
{
    use HasUuids;

    protected $table = 'event_state';

    protected $fillable = [
        'event_id',
        'stage',
        'completion_score',
        'missing_fields',
        'risk_level',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stage' => AgentStage::class,
            'completion_score' => 'integer',
            'missing_fields' => 'array',
            'risk_level' => RiskLevel::class,
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
