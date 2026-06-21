<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

class AuthRedirect
{
    /**
     * The first screen a signed-in user should land on.
     */
    public static function homeFor(User $user): string
    {
        if ($user->isOperational()) {
            return route('operations.home', absolute: false);
        }

        return route('planner', absolute: false);
    }
}
