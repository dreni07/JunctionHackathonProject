<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\Enums\EventRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequestStatusRequest extends FormRequest
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
            'status' => ['required', Rule::enum(EventRequestStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
