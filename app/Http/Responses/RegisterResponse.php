<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
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
