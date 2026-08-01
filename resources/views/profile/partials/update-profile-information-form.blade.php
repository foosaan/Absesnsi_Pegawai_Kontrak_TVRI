<section>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Perbarui nama dan alamat email akun Anda.
    </p>



    <form method="post" action="{{ route('profile.update') }}" id="form-profile-info" class="space-y-4">
        @csrf
        @method('patch')

        <div class="form-group">
            <label for="name" class="form-label dark:text-gray-300">Nama</label>
            <div class="relative">
                <i class="fas fa-user form-control-icon"></i>
                <input id="name" name="name" type="text" 
                       class="form-control form-control-with-icon" 
                       value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" disabled>
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
                       value="{{ old('email', $user->email) }}" required autocomplete="username" disabled>
            </div>
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror


        </div>

        {{-- Tombol Simpan & Batal (hidden default) --}}
        <div class="flex items-center gap-4 pt-2 hidden" id="profile-info-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>
            <button type="button" class="btn btn-secondary" onclick="cancelEditProfile()">
                Batal
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

<script>
    const profileInputs = document.querySelectorAll('#form-profile-info input');
    const profileActions = document.getElementById('profile-info-actions');
    const btnEditProfile = document.getElementById('btn-edit-profile');

    // Simpan nilai awal
    let profileOriginalValues = {};
    profileInputs.forEach(input => {
        profileOriginalValues[input.id] = input.value;
    });

    function toggleEditProfile() {
        profileInputs.forEach(input => input.disabled = false);
        profileActions.classList.remove('hidden');
        if (btnEditProfile) btnEditProfile.classList.add('hidden');
    }

    function cancelEditProfile() {
        profileInputs.forEach(input => {
            input.disabled = true;
            input.value = profileOriginalValues[input.id] || '';
        });
        profileActions.classList.add('hidden');
        if (btnEditProfile) btnEditProfile.classList.remove('hidden');
    }
</script>
