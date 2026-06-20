<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivityLogAction;
use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\FinalProposal;
use App\Models\User;

class ActivityLogService
{
    /**
     * @param  array<string, mixed>|null  $properties
     */
    public function record(
        ActivityLogAction $action,
        string $description,
        ?User $user = null,
        ?EventRequest $eventRequest = null,
        ?Event $event = null,
        ?FinalProposal $proposal = null,
        ?array $properties = null,
    ): ActivityLog {
        return ActivityLog::query()->create([
            'user_id' => $user?->id ?? auth()->id(),
            'event_request_id' => $eventRequest?->id ?? $event?->event_request_id,
            'event_id' => $event?->id ?? $proposal?->event_id,
            'final_proposal_id' => $proposal?->id,
            'action' => $action->value,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
