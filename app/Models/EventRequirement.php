<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SetupType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Structured AI memory — the agent's understanding of what an event needs.
 *
 * @property string $id
 * @property string $event_id
 * @property bool $needs_livestream
 * @property bool $needs_catering
 * @property bool $needs_workshops
 * @property SetupType|null $setup_type
 * @property string|null $notes
 */
class EventRequirement extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_id',
        'needs_livestream',
        'needs_catering',
        'needs_workshops',
        'setup_type',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'needs_livestream' => 'boolean',
            'needs_catering' => 'boolean',
            'needs_workshops' => 'boolean',
            'setup_type' => SetupType::class,
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
