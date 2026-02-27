<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            if (auth()->check()) {
                // Logged in but not admin - redirect to their dashboard
                return match(auth()->user()->role) {
                    'staff_psdm' => redirect()->route('staff.psdm.dashboard'),
                    'staff_keuangan' => redirect()->route('staff.keuangan.dashboard'),
                    default => redirect()->route('dashboard'),
                };
            }
            return redirect()->route('login');
        }

        return $next($request);
    }
}
