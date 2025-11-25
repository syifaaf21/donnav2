<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $userRoleNames = Auth::user()->roles->pluck('name')->map(fn($n) => strtolower($n))->toArray();
        $allowed = array_map('strtolower', $roles);

        if (count(array_intersect($userRoleNames, $allowed)) === 0) {
            abort(403, 'Unauthorized');
        }
        return $next($request);
    }
}

