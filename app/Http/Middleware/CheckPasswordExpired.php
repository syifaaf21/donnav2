<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

class CheckPasswordExpired
{
    /**
     * Handle an incoming request.
     * If the user's password is older than 3 months (or null), redirect to profile page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            // Never expire for Admin and Super Admin
            $roles = $user->roles()->pluck('name')->map(fn($n) => strtolower($n))->toArray();
            if (in_array('admin', $roles) || in_array('super admin', $roles)) {
                return $next($request);
            }

            // Use password_changed_at if present, otherwise fallback to created_at
            $changed = $user->password_changed_at ?? $user->created_at;

            $expired = false;
            if (!is_null($changed)) {
                $expiresAt = Carbon::parse($changed)->addMonths(3);
                if (Carbon::now()->greaterThan($expiresAt)) {
                    $expired = true;
                }
            }

            if ($expired) {
                $currentRoute = Route::currentRouteName();
                // Allow access to profile page and password update route and logout
                $allowed = [
                    'profile.index',
                    'profile.update',
                    'profile.updatePassword',
                    'logout',
                ];

                if (!in_array($currentRoute, $allowed)) {
                    return redirect()->route('profile.index')->with('warning', 'Your password has expired. Please change your password.');
                }
            }
        }

        return $next($request);
    }
}
