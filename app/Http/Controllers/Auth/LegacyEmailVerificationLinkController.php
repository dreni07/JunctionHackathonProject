<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Http\Requests\VerifyEmailRequest;

/**
 * Legacy signed links are no longer used — redirect users to the code screen.
 */
class LegacyEmailVerificationLinkController extends Controller
{
    public function __invoke(VerifyEmailRequest $request): RedirectResponse
    {
        return redirect()
            ->route('verification.notice')
            ->with('status', 'verification-code-required');
    }
}
