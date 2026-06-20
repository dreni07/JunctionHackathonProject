<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\Enums\QuotationLineCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuotationLineItemRequest extends FormRequest
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
            'description' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(QuotationLineCategory::class)],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
