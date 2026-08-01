<x-guest-layout title="Verifikasi 2FA">
    <div class="card">
        <div class="card-body">
            {{-- Header --}}
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900">
                    <i class="fas fa-lock text-2xl text-amber-600 dark:text-amber-400"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Verifikasi 2FA</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                    Masukkan kode 6 digit dari aplikasi authenticator Anda.
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

            <form method="POST" action="{{ route('2fa.authenticate') }}">
                @csrf
                <div class="form-field">
                    <label for="otp" class="form-label">Kode OTP</label>
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
                </div>

                <div class="form-field">
                    <button type="submit" class="btn btn-primary w-full py-2.5">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Verifikasi</span>
                    </button>
                </div>
            </form>

            {{-- Recovery Code Link --}}
            <div class="mt-4 flex flex-col items-center gap-2 text-sm">
                <a href="{{ route('2fa.recovery.form') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">
                    <i class="fas fa-life-ring mr-1"></i> Gunakan recovery code
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
