<x-app-layout title="Ajukan Cuti">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('user.leaves') }}" class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 transition">
                <i class="fas fa-arrow-left text-gray-600 dark:text-gray-400"></i>
            </a>
            <div>
                <h1 class="page-title">Ajukan Cuti</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Isi form pengajuan cuti</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('user.leaves.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-field">
                        <label class="form-label">Jenis Cuti</label>
                        <select name="type" required class="form-control @error('type') border-red-500 @enderror">
                            <option value="">-- Pilih Jenis Cuti --</option>
                            <option value="cuti_tahunan" {{ old('type') === 'cuti_tahunan' ? 'selected' : '' }}>Cuti Tahunan</option>
                            <option value="sakit" {{ old('type') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="alasan_penting" {{ old('type') === 'alasan_penting' ? 'selected' : '' }}>Alasan Penting</option>
                            <option value="lainnya" {{ old('type') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('type')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-field">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" required
                                   min="{{ date('Y-m-d') }}"
                                   class="form-control @error('start_date') border-red-500 @enderror">
                            @error('start_date')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" required
                                   min="{{ date('Y-m-d') }}"
                                   class="form-control @error('end_date') border-red-500 @enderror">
                            @error('end_date')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="form-field">
                        <label class="form-label">Alasan</label>
                        <textarea name="reason" rows="4" required
                                  class="form-control @error('reason') border-red-500 @enderror"
                                  placeholder="Jelaskan alasan pengajuan cuti...">{{ old('reason') }}</textarea>
                        @error('reason')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- File Upload --}}
                    <div class="form-field" x-data="{ fileName: null, preview: null }">
                        <label class="form-label">Bukti/Berkas Pendukung <span class="text-gray-400 text-xs">(opsional)</span></label>
                        <div class="relative">
                            <input type="file" name="attachment" id="attachment" 
                                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                   class="hidden"
                                   @change="
                                       fileName = $event.target.files[0]?.name;
                                       if ($event.target.files[0]?.type.startsWith('image/')) {
                                           const reader = new FileReader();
                                           reader.onload = (e) => preview = e.target.result;
                                           reader.readAsDataURL($event.target.files[0]);
                                       } else {
                                           preview = null;
                                       }
                                   ">
                            <label for="attachment" 
                                   class="flex items-center gap-3 p-4 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-lg cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-slate-700 shrink-0">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 dark:text-gray-500"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="fileName || 'Klik untuk upload file'"></p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">JPG, PNG, PDF, DOC — Max 5MB</p>
                                </div>
                                <template x-if="fileName">
                                    <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 shrink-0">
                                        <i class="fas fa-check mr-1"></i>Terpilih
                                    </span>
                                </template>
                            </label>
                        </div>
                        {{-- Image preview --}}
                        <template x-if="preview">
                            <div class="mt-3 relative inline-block">
                                <img :src="preview" class="max-h-40 rounded-lg border dark:border-slate-600 shadow-sm">
                                <button type="button" @click="preview = null; fileName = null; document.getElementById('attachment').value = ''" 
                                        class="absolute -top-2 -right-2 h-5 w-5 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                        @error('attachment')<p class="form-error">{{ $message }}</p>@enderror
                        <p class="text-xs text-gray-400 mt-1">Contoh: surat dokter, undangan, dokumen pendukung lainnya</p>
                    </div>

                    <div class="notification notification-info mb-6">
                        <h4 class="font-bold mb-2"><i class="fas fa-info-circle mr-1"></i>Informasi:</h4>
                        <ul class="text-sm space-y-1">
                            <li>• Pengajuan cuti akan diproses oleh Staff PSDM</li>
                            <li>• Anda dapat membatalkan pengajuan selama status masih "Menunggu"</li>
                            <li>• Lampirkan bukti pendukung jika diperlukan</li>
                        </ul>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Ajukan Cuti
                        </button>
                        <a href="{{ route('user.leaves') }}" class="btn btn-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
