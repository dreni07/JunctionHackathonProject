<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\Enums\TaskPhase;
use App\Enums\TaskState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'phase' => ['sometimes', Rule::enum(TaskPhase::class)],
            'state' => ['sometimes', Rule::enum(TaskState::class)],
            'due_at' => ['sometimes', 'nullable', 'date'],
            'user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];
    }
}
