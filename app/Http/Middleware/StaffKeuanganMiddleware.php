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
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Staff Keuangan.');
        }

        return $next($request);
    }
}
