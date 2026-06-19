<?php

namespace App\Actions\Fortify;

use App\Enums\AccountType;
use App\Enums\RoleName;
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
     * Sign-up only ever creates a normal organization account (an external
     * party that wants to organize events). The display name is derived from
     * the email, so the form only asks for email, phone, and password.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', Password::default()],
        ])->validate();

        $name = isset($input['name']) && trim((string) $input['name']) !== ''
            ? trim((string) $input['name'])
            : Str::of($input['email'])->before('@')->replace(['.', '_', '-'], ' ')->title()->toString();

        $user = User::create([
            'name' => $name,
            'email' => $input['email'],
            'phone' => $input['phone'] ?? null,
            'password' => $input['password'],
            'account_type' => AccountType::Organization->value,
        ]);

        $user->assignRole(RoleName::Organizer);

        return $user;
    }
}
