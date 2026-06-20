<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();

        if ($user !== null && $user->isOperational()) {
            return redirect()->intended(route('operations.home', absolute: false));
        }

        return redirect()->intended(route('planner', absolute: false));
    }
}
