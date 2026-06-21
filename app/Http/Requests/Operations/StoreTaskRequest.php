<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\Enums\TaskPhase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'uuid', 'exists:events,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phase' => ['required', Rule::enum(TaskPhase::class)],
            'due_at' => ['nullable', 'date'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
