<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\Operational\OperationalModelChanged;
use Illuminate\Database\Eloquent\Model;

class OperationalChangeObserver
{
    public function created(Model $model): void
    {
        $this->dispatch($model, 'created');
    }

    public function updated(Model $model): void
    {
        if ($model->wasChanged() === false) {
            return;
        }

        $this->dispatch($model, 'updated', $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->dispatch($model, 'deleted');
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function dispatch(Model $model, string $action, array $changes = []): void
    {
        OperationalModelChanged::dispatch($model, $action, $changes);
    }
}
