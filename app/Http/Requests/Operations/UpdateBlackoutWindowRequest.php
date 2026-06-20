<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlackoutWindowRequest extends FormRequest
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
            'scope' => ['sometimes', 'string', 'max:255'],
            'days' => ['sometimes', 'string', 'max:255'],
            'start_time' => ['sometimes', 'string', 'max:20'],
            'end_time' => ['sometimes', 'string', 'max:20'],
            'reason' => ['sometimes', 'string', 'max:500'],
        ];
    }
}
