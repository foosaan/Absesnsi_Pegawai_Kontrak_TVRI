<x-guest-layout title="Recovery Code">
    <div class="card">
        <div class="card-body">
            {{-- Header --}}
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                    <i class="fas fa-life-ring text-2xl text-red-600 dark:text-red-400"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Recovery Code</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                    Masukkan salah satu recovery code yang Anda simpan saat setup 2FA.
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

            <form method="POST" action="{{ route('2fa.recovery') }}">
                @csrf
                <div class="form-field">
                    <label for="recovery_code" class="form-label">Recovery Code</label>
                    <div class="relative">
                        <span class="form-control-icon">
                            <i class="fas fa-key"></i>
                        </span>
                        <input 
                            id="recovery_code"
                            type="text" 
                            name="recovery_code" 
                            class="form-control form-control-with-icon font-mono" 
                            placeholder="Masukkan recovery code"
                            required 
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-field">
                    <button type="submit" class="btn btn-danger w-full py-2.5">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login dengan Recovery Code</span>
                    </button>
                </div>
            </form>

            {{-- Back to OTP --}}
            <div class="mt-4 flex flex-col items-center gap-2 text-sm">
                <a href="{{ route('2fa.verify') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke input OTP
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
