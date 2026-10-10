<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Usage in routes: ->middleware(EnsureRole::class . ':admin')  or  ':complainant' */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();
        $isAdmin = $user && $user->isAdmin();

        if (($role === 'admin' && ! $isAdmin) || ($role === 'complainant' && $isAdmin)) {
            abort(403, 'You do not have access to this page.');
        }

        return $next($request);
    }
}
