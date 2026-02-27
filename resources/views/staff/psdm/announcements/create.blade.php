<x-app-layout title="Buat Pengumuman">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff.psdm.announcements') }}" class="btn btn-secondary btn-icon">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title dark:page-title-dark">Buat Pengumuman</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tulis pengumuman baru untuk semua karyawan</p>
            </div>
        </div>
    </x-slot>

    <div class="card dark:card-dark max-w-3xl">
        <form method="POST" action="{{ route('staff.psdm.announcements.store') }}">
            @csrf
            <div class="card-body space-y-6">
                <div class="form-group">
                    <label class="form-label">Judul Pengumuman <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" 
                           class="form-control @error('title') border-red-500 @enderror" 
                           placeholder="Contoh: Pengumuman Libur Nasional" required>
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Isi Pengumuman <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="8" 
                              class="form-control @error('content') border-red-500 @enderror" 
                              placeholder="Tulis isi pengumuman di sini..." required>{{ old('content') }}</textarea>
                    @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked 
                               class="w-4 h-4 rounded border-gray-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Aktifkan pengumuman ini</span>
                    </label>
                </div>
            </div>
            <div class="card-body border-t dark:border-slate-700 flex justify-end gap-3">
                <a href="{{ route('staff.psdm.announcements') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Publikasikan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
