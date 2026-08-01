<x-guest-layout title="Setup 2FA">
    <div class="card">
        <div class="card-body">
            {{-- Header --}}
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <i class="fas fa-shield-alt text-2xl text-blue-600 dark:text-blue-400"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Setup Two-Factor Authentication</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                    Scan QR code berikut dengan aplikasi Google Authenticator atau Authy.
                </p>
            </div>

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="notification notification-danger mb-6">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- QR Code --}}
            <div class="mb-6 text-center">
                <div class="inline-block bg-white p-4 rounded-lg shadow-sm">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" 
                         alt="QR Code 2FA" 
                         class="mx-auto"
                         width="200" height="200">
                </div>
            </div>

            {{-- Manual Secret --}}
            <div class="mb-6 p-3 bg-gray-50 dark:bg-slate-700 rounded-lg text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Atau masukkan kode manual:</p>
                <code class="text-sm font-mono font-bold text-gray-900 dark:text-white tracking-wider">{{ $secret }}</code>
            </div>

            {{-- Verify OTP --}}
            <form method="POST" action="{{ route('2fa.enable') }}">
                @csrf
                <div class="form-field">
                    <label for="otp" class="form-label">Masukkan Kode OTP dari Aplikasi</label>
                    <div class="relative">
                        <span class="form-control-icon">
                            <i class="fas fa-key"></i>
                        </span>
                        <input 
                            id="otp"
                            type="text" 
                            name="otp" 
                            class="form-control form-control-with-icon text-center tracking-[0.5em] text-lg font-bold" 
                            placeholder="000000"
                            maxlength="6"
                            pattern="\d{6}"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            required 
                            autofocus
                        >
                    </div>
                    <p class="text-xs text-gray-500 mt-1 text-center">Masukkan 6 digit kode dari Google Authenticator</p>
                </div>

                <div class="form-field">
                    <button type="submit" class="btn btn-primary w-full py-2.5">
                        <i class="fas fa-check-circle"></i>
                        <span>Aktifkan 2FA</span>
                    </button>
                </div>
            </form>

            {{-- Logout --}}
            <div class="mt-4 text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
