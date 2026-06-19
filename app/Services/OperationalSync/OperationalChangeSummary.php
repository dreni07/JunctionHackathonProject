<?php

declare(strict_types=1);

namespace App\Services\OperationalSync;

use Illuminate\Database\Eloquent\Model;

class OperationalChangeSummary
{
    /**
     * @param  array<string, mixed>  $changes
     */
    public function for(Model $model, string $action, array $changes = []): string
    {
        $label = $this->labelFor($model);

        return match ($action) {
            'created' => sprintf('%s was created.', $label),
            'deleted' => sprintf('%s was removed.', $label),
            default => sprintf('%s was updated (%s).', $label, $this->changedAttributesPhrase($changes)),
        };
    }

    private function labelFor(Model $model): string
    {
        foreach (['name', 'title', 'qr_code'] as $attribute) {
            $value = $model->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return sprintf('%s "%s"', class_basename($model), $value);
            }
        }

        return sprintf('%s #%s', class_basename($model), $model->getKey());
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function changedAttributesPhrase(array $changes): string
    {
        $keys = array_keys($changes);

        if ($keys === []) {
            return 'no attribute diff captured';
        }

        return implode(', ', array_slice($keys, 0, 5)).(count($keys) > 5 ? ', …' : '');
    }
}
