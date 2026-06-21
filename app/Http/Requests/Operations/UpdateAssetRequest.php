<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
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
            'current_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(AssetStatus::class)],
            'type' => ['sometimes', Rule::enum(AssetType::class)],
            'assigned_event_id' => ['sometimes', 'nullable', 'uuid', 'exists:events,id'],
        ];
    }
}
