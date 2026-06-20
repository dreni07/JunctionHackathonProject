<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlackoutWindowRequest extends FormRequest
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
            'scope' => ['required', 'string', 'max:255'],
            'days' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'string', 'max:20'],
            'end_time' => ['required', 'string', 'max:20'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}
