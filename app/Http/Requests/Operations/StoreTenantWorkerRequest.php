<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreTenantWorkerRequest extends FormRequest
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
        $assignableRoles = $this->user()?->tenant?->assignableWorkerRoles() ?? [];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'worker_role' => ['required', 'string', Rule::in($assignableRoles)],
        ];
    }
}
