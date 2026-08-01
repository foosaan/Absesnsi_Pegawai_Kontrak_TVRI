<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffKeuanganMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role !== 'staff_keuangan') {
            // Redirect to their appropriate dashboard instead of hard 403
            return match(auth()->user()->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'staff_psdm' => redirect()->route('staff.psdm.dashboard'),
                default => redirect()->route('dashboard'),
            };
        }

        return $next($request);
    }
}
