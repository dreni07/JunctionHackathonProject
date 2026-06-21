<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\Enums\OrganizationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends FormRequest
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
            'type' => ['sometimes', Rule::enum(OrganizationType::class)],
        ];
    }
}
