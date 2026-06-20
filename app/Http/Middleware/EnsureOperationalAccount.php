<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict Pyramid back-office routes to tenant-based operational accounts.
 * External organization accounts must use the planner instead.
 */
class EnsureOperationalAccount
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->isOperational()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(Response::HTTP_FORBIDDEN, 'Operations access is limited to Pyramid operational staff.');
        }

        return redirect()
            ->route('planner')
            ->with('status', 'Operations access is limited to Pyramid operational staff.');
    }
}
