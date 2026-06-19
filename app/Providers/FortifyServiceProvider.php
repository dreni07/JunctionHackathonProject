<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Enums\AccountType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureAuthentication();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Resolve the user for a password login, enforcing the multi-step
     * context the sign-in form collects:
     *  - the organization door rejects operational workers, and
     *  - the operational door requires the chosen tenant + worker role to
     *    match the account on record.
     */
    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(function (Request $request): ?User {
            $user = User::where('email', $request->input(Fortify::username()))->first();

            if (! $user || ! Hash::check((string) $request->input('password'), $user->password)) {
                return null;
            }

            return match ($request->input('account_type')) {
                AccountType::Operational->value => $this->matchesOperationalContext($user, $request) ? $user : null,
                AccountType::Organization->value => $user->isOperational() ? null : $user,
                default => $user,
            };
        });
    }

    /**
     * Whether an operational worker picked the tenant and role on their record.
     */
    private function matchesOperationalContext(User $user, Request $request): bool
    {
        return $user->isOperational()
            && (string) $user->tenant_id === (string) $request->input('tenant_id')
            && $user->worker_role === $request->input('worker_role');
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'status' => $request->session()->get('status'),
            'tenants' => Tenant::query()
                ->orderBy('id')
                ->get(['id', 'title', 'description', 'roles']),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/reset-password', [
            'email' => $request->email,
            'token' => $request->route('token'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/forgot-password', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/verify-email', [
            'status' => $request->session()->get('status'),
            // Seconds the user must wait before another email can be sent
            // (60 right after a send, 0 once the window has elapsed). The key
            // mirrors how the throttle middleware stores a named limiter.
            'resendCooldown' => RateLimiter::availableIn(
                md5('verification'.($request->user() !== null ? (string) $request->user()->id : $request->ip())),
            ),
        ]));

        Fortify::registerView(fn () => Inertia::render('auth/register', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/two-factor-challenge'));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/confirm-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('passkeys', function (Request $request) {
            return Limit::perMinute(10)->by(
                ($request->input('credential.id') ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });

        // Allow at most one verification email per 60 seconds, per user.
        // Hitting this returns HTTP 429 with a Retry-After header that the
        // verify-email screen reads to drive its resend countdown.
        RateLimiter::for('verification', function (Request $request) {
            $key = $request->user() !== null ? (string) $request->user()->id : $request->ip();

            return Limit::perMinute(1)->by($key);
        });
    }
}
