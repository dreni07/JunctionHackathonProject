<?php

declare(strict_types=1);

namespace App\Listeners\Operational;

use App\Events\Operational\OperationalModelChanged;
use App\Models\OperationalChange;
use App\Services\OperationalSync\OperationalChangeSummary;

class RecordOperationalChange
{
    public function __construct(
        private readonly OperationalChangeSummary $summary,
    ) {}

    public function handle(OperationalModelChanged $event): void
    {
        OperationalChange::query()->create([
            'model_type' => $event->modelType(),
            'model_id' => $event->modelId(),
            'action' => $event->action,
            'summary' => $this->summary->for($event->model, $event->action, $event->changes),
            'payload' => $event->payload(),
            'occurred_at' => now(),
        ]);
    }
}
