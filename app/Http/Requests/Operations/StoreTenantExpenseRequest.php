<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\Enums\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantExpenseRequest extends FormRequest
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
            'category' => ['required', Rule::enum(ExpenseCategory::class)],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'incurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
