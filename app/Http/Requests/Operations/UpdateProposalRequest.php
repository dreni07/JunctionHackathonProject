<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\Enums\EventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProposalRequest extends FormRequest
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
            'proposed_space_id' => ['sometimes', 'nullable', 'uuid', 'exists:spaces,id'],
            'proposed_capacity' => ['sometimes', 'integer', 'min:1'],
            'proposed_price' => ['sometimes', 'numeric', 'min:0'],
            'start_at' => ['sometimes', 'date'],
            'end_at' => ['sometimes', 'date', 'after:start_at'],
            'event_type' => ['sometimes', Rule::enum(EventType::class)],
        ];
    }
}
