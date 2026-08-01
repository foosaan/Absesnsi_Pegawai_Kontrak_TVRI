<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     * Supports both email and NIP as identifier.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
        ]);

        $identifier = trim($request->identifier);

        // Determine if input is email or NIP
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $identifier)->first();
            if (!$user) {
                return back()->withInput()->withErrors(['identifier' => 'Email tidak ditemukan dalam sistem.']);
            }
            $email = $user->email;
        } else {
            // Treat as NIP
            $user = User::where('nip', $identifier)->first();
            if (!$user) {
                return back()->withInput()->withErrors(['identifier' => 'NIP tidak ditemukan dalam sistem.']);
            }
            if (!$user->email) {
                return back()->withInput()->withErrors(['identifier' => 'Akun dengan NIP ini tidak memiliki email terdaftar.']);
            }
            $email = $user->email;
        }

        // Send the password reset link
        $status = Password::sendResetLink(['email' => $email]);

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', 'Link reset password telah dikirim ke email: ' . $this->maskEmail($email));
        }

        if ($status == Password::RESET_THROTTLED) {
            return back()->withErrors(['identifier' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.']);
        }

        return back()->withErrors(['identifier' => 'Gagal mengirim link reset. Silakan coba lagi.']);
    }

    /**
     * Mask email for privacy (show first 3 chars + domain)
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        $masked = substr($name, 0, 3) . str_repeat('*', max(strlen($name) - 3, 0));
        return $masked . '@' . $domain;
    }
}
