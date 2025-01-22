<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized action.');
        }

        if (!Auth::user()->role) {
            abort(403, 'User has no assigned role.');
        }

        if (!in_array(Auth::user()->role->name, $roles)) {
            abort(403, 'User does not have the required role.');
        }

        return $next($request);
    }
}
