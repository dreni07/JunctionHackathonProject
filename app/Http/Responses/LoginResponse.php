<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();

        if ($user !== null) {
            return redirect()->intended(AuthRedirect::homeFor($user));
        }

        return redirect()->intended(route('planner', absolute: false));
    }
}
