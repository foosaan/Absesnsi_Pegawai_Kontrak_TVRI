<x-app-layout title="Detail Karyawan - {{ $user->name }}">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                    <i class="fas fa-user text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Detail Karyawan</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->name }}</p>
                </div>
            </div>
            <a href="{{ route('staff.keuangan.users') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Informasi Karyawan --}}
        <div class="card dark:card-dark h-fit">
            <div class="card-header dark:card-header-dark">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-user text-blue-500 mr-2"></i>
                    Informasi Karyawan
                </h3>
            </div>
            <div class="card-body">
                <div class="flex items-center gap-4 mb-6">
                    <div class="avatar avatar-lg bg-blue-600 text-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="font-medium text-lg text-gray-900 dark:text-white">{{ $user->name }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2 border-b dark:border-slate-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">NIP</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->nip ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b dark:border-slate-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">No. HP</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->no_hp ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b dark:border-slate-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Bagian</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->bagian ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Jabatan</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->jabatan ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Keuangan --}}
        <div class="card dark:card-dark h-fit">
            <div class="card-header dark:card-header-dark">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-wallet text-emerald-500 mr-2"></i>
                    Data Keuangan
                </h3>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2 border-b dark:border-slate-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Gaji Pokok</span>
                        <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($user->gaji_pokok ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b dark:border-slate-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Nama Bank</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->nama_bank ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b dark:border-slate-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">No. Rekening</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->no_rekening ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Status Pegawai</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->status_pegawai ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info banner --}}
    <div class="mt-6 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
        <div class="flex items-center gap-3">
            <i class="fas fa-info-circle text-blue-500 dark:text-blue-400"></i>
            <p class="text-sm text-blue-700 dark:text-blue-300">
                Untuk mengedit data karyawan, silakan hubungi <strong>Staff PSDM</strong>.
            </p>
        </div>
    </div>
</x-app-layout>
