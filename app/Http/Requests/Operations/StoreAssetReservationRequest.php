<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetReservationRequest extends FormRequest
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
            'asset_id' => ['required', 'uuid', 'exists:assets,id'],
            'reserved_quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
