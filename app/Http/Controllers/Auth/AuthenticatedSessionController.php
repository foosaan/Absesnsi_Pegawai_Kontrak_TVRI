<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view (User).
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Display the staff login view.
     */
    public function createStaff(): View
    {
        return view('auth.staff-login');
    }

    /**
     * Display the admin login view.
     */
    public function createAdmin(): View
    {
        return view('auth.admin-login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $loginType = $request->input('login_type', 'user');

        // Validate role matches login page
        $allowed = match($loginType) {
            'admin' => ['admin'],
            'staff' => ['staff_psdm', 'staff_keuangan'],
            'user'  => ['user'],
            default => ['user'],
        };

        if (!in_array($user->role, $allowed)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $loginRoute = match($loginType) {
                'admin' => 'admin.login',
                'staff' => 'staff.login',
                default => 'login',
            };

            return redirect()->route($loginRoute)
                ->withErrors(['email' => 'Akun Anda tidak memiliki akses di halaman ini.']);
        }

        // Redirect to correct dashboard
        return match($user->role) {
            'admin' => redirect()->intended(route('admin.dashboard')),
            'staff_psdm' => redirect()->intended(route('staff.psdm.dashboard')),
            'staff_keuangan' => redirect()->intended(route('staff.keuangan.dashboard')),
            default => redirect()->intended(route('dashboard')),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $role = Auth::user()->role ?? 'user';

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Redirect to the correct login page
        return match($role) {
            'admin' => redirect()->route('admin.login'),
            'staff_psdm', 'staff_keuangan' => redirect()->route('staff.login'),
            default => redirect('/'),
        };
    }
}
