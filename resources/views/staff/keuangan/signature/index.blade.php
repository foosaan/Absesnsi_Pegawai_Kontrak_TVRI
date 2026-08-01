<x-app-layout title="Tanda Tangan">
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900">
                <i class="fas fa-file-signature text-indigo-600 dark:text-indigo-400"></i>
            </div>
            <div>
                <h1 class="page-title dark:page-title-dark">Tanda Tangan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola tanda tangan untuk slip gaji</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        {{-- Current Signature Status --}}
        <div class="card dark:card-dark mb-6">
            <div class="card-header">
                <h3 class="card-title flex items-center gap-2">
                    <i class="fas fa-image text-sm text-gray-400"></i>
                    Tanda Tangan Saat Ini
                </h3>
            </div>
            <div class="card-body">
                @if(auth()->user()->signature)
                    <div class="flex flex-col items-center gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-slate-800 rounded-xl border-2 border-dashed border-gray-200 dark:border-slate-600">
                            <img src="{{ asset('storage/' . auth()->user()->signature) }}" 
                                 alt="Tanda Tangan" 
                                 class="max-h-32 w-auto">
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-medium">
                                <i class="fas fa-check-circle text-xs"></i>
                                Aktif
                            </span>
                            <span class="text-gray-400 dark:text-gray-500">—</span>
                            <span class="text-gray-500 dark:text-gray-400">Tanda tangan Anda akan tampil di slip gaji yang ditandatangani</span>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center gap-3 py-6">
                        <div class="h-16 w-16 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="fas fa-signature text-2xl text-amber-500 dark:text-amber-400"></i>
                        </div>
                        <div class="text-center">
                            <p class="font-medium text-gray-700 dark:text-gray-300">Belum Ada Tanda Tangan</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Upload tanda tangan Anda agar bisa menandatangani slip gaji</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Upload / Replace Form --}}
        <div class="card dark:card-dark">
            <div class="card-header">
                <h3 class="card-title flex items-center gap-2">
                    <i class="fas fa-upload text-sm text-gray-400"></i>
                    {{ auth()->user()->signature ? 'Ganti Tanda Tangan' : 'Upload Tanda Tangan' }}
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('staff.keuangan.signature.upload') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="space-y-4">
                        {{-- File Input --}}
                        <div>
                            <label class="form-label">File Gambar Tanda Tangan</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-xl hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors" 
                                 id="drop-zone">
                                <div class="space-y-2 text-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 dark:text-gray-500"></i>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <label for="signature-input" class="relative cursor-pointer rounded-md font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 focus-within:outline-none">
                                            <span>Pilih file</span>
                                            <input id="signature-input" name="signature" type="file" accept="image/jpeg,image/png,image/jpg" required class="sr-only">
                                        </label>
                                        <span>atau drag & drop</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">JPG, JPEG, atau PNG (maks. 2MB)</p>
                                </div>
                            </div>
                            @error('signature')
                                <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Preview --}}
                        <div id="preview-container" class="hidden">
                            <label class="form-label">Preview</label>
                            <div class="p-4 bg-gray-50 dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-600">
                                <img id="preview-image" src="" alt="Preview" class="max-h-24 w-auto mx-auto">
                            </div>
                            <p id="file-name" class="text-xs text-gray-500 dark:text-gray-400 mt-1.5"></p>
                        </div>

                        {{-- Tips --}}
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
                            <p class="text-xs font-medium text-blue-700 dark:text-blue-400 mb-1.5">
                                <i class="fas fa-lightbulb mr-1"></i> Tips
                            </p>
                            <ul class="text-xs text-blue-600 dark:text-blue-400 space-y-1 list-disc list-inside">
                                <li>Gunakan gambar dengan latar belakang putih atau transparan</li>
                                <li>Pastikan tanda tangan jelas dan tidak terpotong</li>
                                <li>Ukuran minimum disarankan 200x100 piksel</li>
                            </ul>
                        </div>

                        {{-- Submit --}}
                        <div class="flex items-center gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload mr-2"></i>
                                {{ auth()->user()->signature ? 'Ganti Tanda Tangan' : 'Upload Tanda Tangan' }}
                            </button>
                            @if(auth()->user()->signature)
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Tanda tangan lama akan diganti
                                </span>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const input = document.getElementById('signature-input');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');
        const fileName = document.getElementById('file-name');
        const dropZone = document.getElementById('drop-zone');

        input.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
                fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            }
        });

        // Drag & drop
        ['dragenter', 'dragover'].forEach(evt => {
            dropZone.addEventListener(evt, e => {
                e.preventDefault();
                dropZone.classList.add('border-indigo-400', 'bg-indigo-50', 'dark:bg-indigo-900/10');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropZone.addEventListener(evt, e => {
                e.preventDefault();
                dropZone.classList.remove('border-indigo-400', 'bg-indigo-50', 'dark:bg-indigo-900/10');
            });
        });
        dropZone.addEventListener('drop', e => {
            const files = e.dataTransfer.files;
            if (files.length) {
                input.files = files;
                input.dispatchEvent(new Event('change'));
            }
        });
    </script>
    @endpush
</x-app-layout>
