<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\Enums\EventStatus;
use App\Enums\EventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'start_time' => ['sometimes', 'date'],
            'end_time' => ['sometimes', 'date', 'after:start_time'],
            'budget' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'attendees' => ['sometimes', 'integer', 'min:1'],
            'event_type' => ['sometimes', Rule::enum(EventType::class)],
            'status' => ['sometimes', Rule::enum(EventStatus::class)],
        ];
    }
}
