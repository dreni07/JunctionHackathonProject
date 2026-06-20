<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantFinanceProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTenantManager() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'annual_budget' => ['required', 'numeric', 'min:0'],
            'operating_reserve' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
