<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class EmailVerificationCodeService
{
    private const CODE_LENGTH = 6;

    /**
     * Issue a fresh numeric verification code for the user.
     */
    public function issue(User $user): string
    {
        $code = str_pad(
            (string) random_int(0, 10 ** self::CODE_LENGTH - 1),
            self::CODE_LENGTH,
            '0',
            STR_PAD_LEFT,
        );

        Cache::put(
            $this->cacheKey($user),
            Hash::make($code),
            now()->addMinutes($this->expiryMinutes()),
        );

        return $code;
    }

    /**
     * Whether the user already has an unexpired code in cache.
     */
    public function hasActiveCode(User $user): bool
    {
        return Cache::has($this->cacheKey($user));
    }

    /**
     * Validate the submitted code and invalidate it on success.
     */
    public function verify(User $user, string $code): bool
    {
        $hashedCode = Cache::get($this->cacheKey($user));

        if (! is_string($hashedCode)) {
            return false;
        }

        if (! Hash::check($code, $hashedCode)) {
            return false;
        }

        Cache::forget($this->cacheKey($user));

        return true;
    }

    private function cacheKey(User $user): string
    {
        return 'email-verification-code:'.$user->getKey();
    }

    private function expiryMinutes(): int
    {
        return (int) config('auth.verification.expire', 15);
    }
}
