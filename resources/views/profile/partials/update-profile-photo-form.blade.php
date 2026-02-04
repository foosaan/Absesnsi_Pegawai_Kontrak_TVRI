<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Foto Profil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Klik foto untuk mengubah.') }}
        </p>
    </header>

    <div class="mt-6 flex items-center gap-6">
        {{-- Current Photo (Clickable) --}}
        <div class="shrink-0 relative group cursor-pointer" onclick="document.getElementById('photo').click()">
            <img class="h-24 w-24 object-cover rounded-full border-4 border-gray-200 group-hover:opacity-75 transition" 
                 src="{{ Auth::user()->getProfilePhotoUrl() }}" 
                 alt="{{ Auth::user()->name }}"
                 id="photo-preview">
            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                <span class="bg-black/50 text-white text-xs px-2 py-1 rounded">📷 Ubah</span>
            </div>
        </div>

        <div class="flex-1">
            {{-- Upload Form (Auto-submit) --}}
            <form method="post" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" id="photo-form">
                @csrf

                <input type="file" 
                       name="photo" 
                       id="photo"
                       accept="image/*"
                       class="hidden"
                       onchange="handlePhotoChange(this)">
                
                <div class="text-sm text-gray-600 mb-2">
                    <button type="button" onclick="document.getElementById('photo').click()" 
                            class="text-indigo-600 hover:text-indigo-800 font-medium">
                        Pilih foto baru
                    </button>
                </div>
                
                @error('photo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500">Format: JPG, PNG, GIF. Maks: 2MB</p>

                {{-- Loading indicator --}}
                <div id="upload-loading" class="hidden mt-2">
                    <span class="text-sm text-indigo-600">⏳ Mengupload...</span>
                </div>

                @if (session('status') === 'photo-updated')
                    <p x-data="{ show: true }"
                       x-show="show"
                       x-transition
                       x-init="setTimeout(() => show = false, 3000)"
                       class="text-sm text-green-600 mt-2">✅ {{ __('Foto berhasil diupdate.') }}</p>
                @endif
            </form>

            {{-- Delete Photo --}}
            @if(Auth::user()->profile_photo)
            <form method="post" action="{{ route('profile.photo.delete') }}" class="mt-3">
                @csrf
                @method('delete')
                <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                    🗑️ {{ __('Hapus Foto') }}
                </button>
            </form>
            @endif
        </div>
    </div>

    <script>
        function handlePhotoChange(input) {
            if (input.files && input.files[0]) {
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photo-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);

                // Show loading
                document.getElementById('upload-loading').classList.remove('hidden');
                
                // Auto submit form
                document.getElementById('photo-form').submit();
            }
        }
    </script>
</section>
