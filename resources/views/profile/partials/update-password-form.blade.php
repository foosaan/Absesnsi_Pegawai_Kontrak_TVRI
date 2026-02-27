<section>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Gunakan password yang panjang dan unik agar akun Anda tetap aman.
    </p>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
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

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-green-600 dark:text-green-400">
                    <i class="fas fa-check-circle mr-1"></i> Password berhasil diubah.
                </p>
            @endif
        </div>
    </form>
</section>
