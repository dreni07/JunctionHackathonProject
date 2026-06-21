<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property OrganizationType|null $type
 */
class Organization extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'type',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => OrganizationType::class,
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<EventRequest, $this>
     */
    public function eventRequests(): HasMany
    {
        return $this->hasMany(EventRequest::class);
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * @return HasMany<FinalProposal, $this>
     */
    public function finalProposals(): HasMany
    {
        return $this->hasMany(FinalProposal::class);
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
