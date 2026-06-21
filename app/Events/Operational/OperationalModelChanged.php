<?php

declare(strict_types=1);

namespace App\Events\Operational;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OperationalModelChanged
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, mixed>  $changes
     */
    public function __construct(
        public Model $model,
        public string $action,
        public array $changes = [],
    ) {}

    public function modelType(): string
    {
        return class_basename($this->model);
    }

    public function modelId(): string
    {
        return (string) $this->model->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'model_type' => $this->modelType(),
            'model_id' => $this->modelId(),
            'action' => $this->action,
            'changes' => $this->changes,
        ];
    }
}
