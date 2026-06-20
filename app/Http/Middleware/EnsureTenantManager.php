<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantManager
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isTenantManager()) {
            if ($request->expectsJson()) {
                abort(403, 'Tenant manager access required.');
            }

            return redirect()->route('operations.home');
        }

        return $next($request);
    }
}
