<x-app-layout title="Edit Data Keuangan - {{ $user->name }}">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900">
                    <i class="fas fa-user-edit text-amber-600 dark:text-amber-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Edit Data Keuangan</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->name }}</p>
                </div>
            </div>
            <a href="{{ route('staff.keuangan.users') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('staff.keuangan.users.update', $user) }}" class="grid gap-6 lg:grid-cols-2">
        @csrf
        @method('PUT')

        {{-- Personal Info (Readonly) --}}
        <div class="card dark:card-dark h-fit">
            <div class="card-header dark:card-header-dark">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-user text-blue-500 mr-2"></i>
                    Informasi Karyawan
                </h3>
            </div>
            <div class="card-body space-y-4">
                <div class="flex items-center gap-4 mb-4">
                    <div class="avatar avatar-lg bg-blue-600 text-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="font-medium text-lg text-gray-900 dark:text-white">{{ $user->name }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">NIP</label>
                        <input type="text" class="form-control bg-gray-100 dark:bg-slate-700 cursor-not-allowed" 
                               value="{{ $user->nip ?? '-' }}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP</label>
                        <input type="text" class="form-control bg-gray-100 dark:bg-slate-700 cursor-not-allowed" 
                               value="{{ $user->no_hp ?? '-' }}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bagian</label>
                        <input type="text" class="form-control bg-gray-100 dark:bg-slate-700 cursor-not-allowed" 
                               value="{{ $user->bagian ?? '-' }}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jabatan</label>
                        <input type="text" class="form-control bg-gray-100 dark:bg-slate-700 cursor-not-allowed" 
                               value="{{ $user->jabatan ?? '-' }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- Financial Info --}}
        <div class="card dark:card-dark h-fit">
            <div class="card-header dark:card-header-dark">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-wallet text-emerald-500 mr-2"></i>
                    Data Keuangan
                </h3>
            </div>
            <div class="card-body space-y-4">
                <div class="form-group">
                    <label class="form-label">Gaji Pokok</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="number" name="gaji_pokok" 
                               class="form-control pl-10 @error('gaji_pokok') border-red-500 @enderror" 
                               value="{{ old('gaji_pokok', $user->gaji_pokok) }}" required>
                    </div>
                    @error('gaji_pokok')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Nomor Rekening</label>
                    <div class="relative">
                        <i class="fas fa-credit-card input-icon"></i>
                        <input type="text" name="no_rekening" 
                               class="form-control form-control-with-icon" 
                               value="{{ old('no_rekening', $user->no_rekening) }}" placeholder="Contoh: 1234567890">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Bank</label>
                    <div class="relative">
                        <i class="fas fa-university input-icon"></i>
                        <input type="text" name="nama_bank" 
                               class="form-control form-control-with-icon" 
                               value="{{ old('nama_bank', $user->nama_bank) }}" placeholder="Contoh: Bank BRI">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fas fa-save"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
