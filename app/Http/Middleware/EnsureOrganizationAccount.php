<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict planner and event-scheduling routes to external organization accounts.
 * Pyramid operational staff must use the operations dashboard instead.
 */
class EnsureOrganizationAccount
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->isOrganization()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(Response::HTTP_FORBIDDEN, 'Planner access is limited to organization accounts.');
        }

        return redirect()
            ->route('operations.home')
            ->with('status', 'Planner access is limited to organization accounts.');
    }
}
