<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     * Only allow users with role 'user' to access employee routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role !== 'user') {
            // Redirect staff/admin to their own dashboard
            return match(auth()->user()->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'staff_psdm' => redirect()->route('staff.psdm.dashboard'),
                'staff_keuangan' => redirect()->route('staff.keuangan.dashboard'),
                default => redirect()->route('dashboard'),
            };
        }

        return $next($request);
    }
}
