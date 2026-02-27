<section>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Perbarui nama dan alamat email akun Anda.
    </p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <div class="form-group">
            <label for="name" class="form-label dark:text-gray-300">Nama</label>
            <div class="relative">
                <i class="fas fa-user form-control-icon"></i>
                <input id="name" name="name" type="text" 
                       class="form-control form-control-with-icon" 
                       value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            </div>
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label dark:text-gray-300">Email</label>
            <div class="relative">
                <i class="fas fa-envelope form-control-icon"></i>
                <input id="email" name="email" type="email" 
                       class="form-control form-control-with-icon" 
                       value="{{ old('email', $user->email) }}" required autocomplete="username">
            </div>
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Email Anda belum diverifikasi.
                        <button form="send-verification" class="underline text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            Klik untuk mengirim ulang email verifikasi.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm text-green-600 dark:text-green-400">
                            <i class="fas fa-check-circle mr-1"></i> Link verifikasi baru telah dikirim.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-green-600 dark:text-green-400">
                    <i class="fas fa-check-circle mr-1"></i> Tersimpan.
                </p>
            @endif
        </div>
    </form>
</section>
