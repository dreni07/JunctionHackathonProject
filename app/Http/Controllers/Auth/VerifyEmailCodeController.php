<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailCodeRequest;
use App\Models\User;
use App\Services\EmailVerificationCodeService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class VerifyEmailCodeController extends Controller
{
    public function __construct(private EmailVerificationCodeService $verificationCodes) {}

    /**
     * Verify the authenticated user's email using a one-time code.
     */
    public function store(VerifyEmailCodeRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($this->homeFor($user));
        }

        if (! $this->verificationCodes->verify($user, $request->validated('code'))) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid or has expired.',
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($this->homeFor($user).'?verified=1');
    }

    private function homeFor(User $user): string
    {
        if ($user->isOperational()) {
            return route('operations.home', absolute: false);
        }

        return route('planner', absolute: false);
    }
}
