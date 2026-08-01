<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     * Enforce 2FA for admin users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip if not authenticated, not admin, or running unit tests
        if (!$user || !$user->requiresTwoFactor() || app()->runningUnitTests()) {
            return $next($request);
        }

        // Allow access to 2FA routes, logout, and theme toggle
        $allowedRoutes = ['2fa.setup', '2fa.enable', '2fa.verify', '2fa.authenticate', '2fa.recovery', '2fa.recovery.form', 'logout', 'theme.toggle'];
        if (in_array($request->route()?->getName(), $allowedRoutes)) {
            return $next($request);
        }

        // If 2FA not setup yet, force setup
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('2fa.setup');
        }

        // If 2FA not verified this session, force verify
        if (!session('2fa_verified')) {
            return redirect()->route('2fa.verify');
        }

        return $next($request);
    }
}
