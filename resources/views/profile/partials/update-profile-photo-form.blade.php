<section class="flex flex-col sm:flex-row items-center gap-6">
    {{-- Photo --}}
    <div class="shrink-0 relative group cursor-pointer" onclick="document.getElementById('photo').click()">
        <img class="h-28 w-28 object-cover rounded-full border-4 border-gray-200 dark:border-slate-600 group-hover:opacity-75 transition" 
             src="{{ Auth::user()->getProfilePhotoUrl() }}" 
             alt="{{ Auth::user()->name }}"
             id="photo-preview">
        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
            <span class="bg-black/60 text-white text-xs px-3 py-1.5 rounded-full">
                <i class="fas fa-camera mr-1"></i> Ubah
            </span>
        </div>
    </div>

    {{-- Info & Actions --}}
    <div class="flex-1 text-center sm:text-left">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ Auth::user()->name }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
        
        @php
            $roleLabels = [
                'admin' => ['Admin', 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'],
                'staff_psdm' => ['Staff PSDM', 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'],
                'staff_keuangan' => ['Staff Keuangan', 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'],
                'user' => ['Karyawan', 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
            ];
            $role = Auth::user()->role ?? 'user';
            $label = $roleLabels[$role] ?? $roleLabels['user'];
        @endphp
        <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold {{ $label[1] }}">
            {{ $label[0] }}
        </span>

        <div class="mt-3 flex flex-wrap gap-2 justify-center sm:justify-start">
            {{-- Upload Form --}}
            <form method="post" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" id="photo-form">
                @csrf
                <input type="file" name="photo" id="photo" accept="image/*" class="hidden" onchange="handlePhotoChange(this)">
                <button type="button" onclick="document.getElementById('photo').click()" class="btn btn-primary btn-sm">
                    <i class="fas fa-upload mr-1"></i> Upload Foto
                </button>
            </form>

            @if(Auth::user()->profile_photo)
            <form method="post" action="{{ route('profile.photo.delete') }}">
                @csrf
                @method('delete')
                <button type="submit" class="btn btn-secondary btn-sm text-red-600 dark:text-red-400">
                    <i class="fas fa-trash mr-1"></i> Hapus
                </button>
            </form>
            @endif
        </div>

        @error('photo')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Format: JPG, PNG, GIF. Maks: 2MB</p>

        {{-- Loading indicator --}}
        <div id="upload-loading" class="hidden mt-2">
            <span class="text-sm text-blue-500"><i class="fas fa-spinner fa-spin mr-1"></i> Mengupload...</span>
        </div>

        @if (session('status') === 'photo-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
               class="text-sm text-green-600 dark:text-green-400 mt-2">
                <i class="fas fa-check-circle mr-1"></i> Foto berhasil diupdate.
            </p>
        @endif
    </div>
</section>

<script>
    function handlePhotoChange(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photo-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
            document.getElementById('upload-loading').classList.remove('hidden');
            document.getElementById('photo-form').submit();
        }
    }
</script>
