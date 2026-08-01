<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Tampilkan halaman setup 2FA dengan QR code
     */
    public function setup(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Jika sudah aktif, alihkan kembali ke dashboard
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard')
                ->with('info', '2FA sudah aktif.');
        }

        // Buat secret key jika belum ada
        $secret = $user->two_factor_secret ?: $this->google2fa->generateSecretKey();

        // Simpan secret key sementara (belum diaktifkan sepenuhnya)
        if (!$user->two_factor_secret) {
            $user->update(['two_factor_secret' => $secret]);
        }

        // Buat URL QR code secara inline
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'TVRI Presensi'),
            $user->email,
            $secret
        );

        return view('auth.two-factor.setup', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    /**
     * Aktifkan 2FA setelah verifikasi kode OTP pertama
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = $request->user();
        $secret = $user->two_factor_secret;

        if (!$secret) {
            return back()->withErrors(['otp' => 'Secret key tidak ditemukan. Silakan mulai setup ulang.']);
        }

        // Verifikasi OTP
        $valid = $this->google2fa->verifyKey($secret, $request->otp);

        if (!$valid) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid. Pastikan waktu di HP Anda sudah sinkron.']);
        }

        // Buat kode pemulihan (recovery codes)
        $recoveryCodes = collect(range(1, 8))->map(fn() => Str::random(10))->toArray();

        // Aktifkan 2FA
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ]);

        // Tandai sesi ini sebagai terverifikasi 2FA
        session(['2fa_verified' => true]);

        return redirect()->route('admin.dashboard')
            ->with('success', '2FA berhasil diaktifkan! Simpan recovery codes Anda.')
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Tampilkan formulir verifikasi 2FA (saat login)
     */
    public function verify(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Jika bukan admin atau sudah terverifikasi, lewati
        if (!$user->requiresTwoFactor() || session('2fa_verified')) {
            return redirect()->route('admin.dashboard');
        }

        // Jika 2FA belum di-setup, alihkan ke halaman setup
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('2fa.setup');
        }

        return view('auth.two-factor.verify');
    }

    /**
     * Autentikasi dengan kode OTP
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = $request->user();

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->otp);

        if (!$valid) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid.']);
        }

        session(['2fa_verified' => true]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Verifikasi 2FA berhasil!');
    }

    /**
     * Tampilkan formulir kode pemulihan
     */
    public function showRecoveryForm(): View
    {
        return view('auth.two-factor.recovery');
    }

    /**
     * Autentikasi dengan kode pemulihan
     */
    public function useRecoveryCode(Request $request): RedirectResponse
    {
        $request->validate([
            'recovery_code' => 'required|string',
        ]);

        $user = $request->user();
        $codes = json_decode($user->two_factor_recovery_codes, true) ?: [];

        $code = trim($request->recovery_code);

        if (!in_array($code, $codes)) {
            return back()->withErrors(['recovery_code' => 'Recovery code tidak valid.']);
        }

        // Hapus kode pemulihan yang sudah digunakan
        $codes = array_values(array_filter($codes, fn($c) => $c !== $code));
        $user->update(['two_factor_recovery_codes' => json_encode($codes)]);

        session(['2fa_verified' => true]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Login berhasil dengan recovery code. Sisa recovery codes: ' . count($codes));
    }
}
