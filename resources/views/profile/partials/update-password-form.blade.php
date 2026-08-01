<section>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Gunakan password yang panjang dan unik agar akun Anda tetap aman.
    </p>

    {{-- Form password (hidden default, muncul saat klik Edit) --}}
    <div id="password-form-wrapper" class="hidden">
        <form method="post" action="{{ route('password.update') }}" id="form-password" class="space-y-4">
            @csrf
            @method('put')

            <div class="form-group">
                <label for="update_password_current_password" class="form-label dark:text-gray-300">Password Saat Ini</label>
                <div class="relative">
                    <i class="fas fa-key form-control-icon"></i>
                    <input id="update_password_current_password" name="current_password" type="password" 
                           class="form-control form-control-with-icon" autocomplete="current-password">
                </div>
                @error('current_password', 'updatePassword')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="update_password_password" class="form-label dark:text-gray-300">Password Baru</label>
                <div class="relative">
                    <i class="fas fa-lock form-control-icon"></i>
                    <input id="update_password_password" name="password" type="password" 
                           class="form-control form-control-with-icon" autocomplete="new-password">
                </div>
                @error('password', 'updatePassword')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="update_password_password_confirmation" class="form-label dark:text-gray-300">Konfirmasi Password</label>
                <div class="relative">
                    <i class="fas fa-lock form-control-icon"></i>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                           class="form-control form-control-with-icon" autocomplete="new-password">
                </div>
                @error('password_confirmation', 'updatePassword')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
                <button type="button" class="btn btn-secondary" onclick="cancelEditPassword()">
                    Batal
                </button>

                @if (session('status') === 'password-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                       class="text-sm text-green-600 dark:text-green-400">
                        <i class="fas fa-check-circle mr-1"></i> Password berhasil diubah.
                    </p>
                @endif
            </div>
        </form>
    </div>

    {{-- Placeholder saat form hidden --}}
    <div id="password-placeholder">
        <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 dark:bg-slate-700/50">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                <i class="fas fa-shield-alt text-yellow-600 dark:text-yellow-400"></i>
            </div>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Password terlindungi</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Klik tombol "Ubah Password" untuk mengganti password Anda</p>
            </div>
        </div>
    </div>
</section>

<script>
    const passwordFormWrapper = document.getElementById('password-form-wrapper');
    const passwordPlaceholder = document.getElementById('password-placeholder');
    const btnEditPassword = document.getElementById('btn-edit-password');

    // Jika ada validation error, otomatis buka form
    @if($errors->updatePassword->any())
        toggleEditPassword();
    @endif

    function toggleEditPassword() {
        passwordFormWrapper.classList.remove('hidden');
        passwordPlaceholder.classList.add('hidden');
        if (btnEditPassword) btnEditPassword.classList.add('hidden');
    }

    function cancelEditPassword() {
        passwordFormWrapper.classList.add('hidden');
        passwordPlaceholder.classList.remove('hidden');
        if (btnEditPassword) btnEditPassword.classList.remove('hidden');
        // Clear password fields
        document.getElementById('form-password').reset();
    }
</script>
