<?php

namespace App\Actions\Fortify;

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * Public sign-up always creates an external organization account.
     * Operational workers are provisioned separately — never through this form.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', Password::default()],
            'account_type' => ['prohibited'],
            'tenant_id' => ['prohibited'],
            'worker_role' => ['prohibited'],
        ])->validate();

        $name = isset($input['name']) && trim((string) $input['name']) !== ''
            ? trim((string) $input['name'])
            : Str::of($input['email'])->before('@')->replace(['.', '_', '-'], ' ')->title()->toString();

        $organization = Organization::query()->create([
            'name' => $name,
        ]);

        $user = User::create([
            'name' => $name,
            'email' => $input['email'],
            'phone' => $input['phone'] ?? null,
            'password' => $input['password'],
            'organization_id' => $organization->id,
            'account_type' => AccountType::Organization->value,
            'tenant_id' => null,
            'worker_role' => null,
        ]);

        $user->syncRoles(RoleName::Organizer);

        return $user;
    }
}
