<x-app-layout title="Admin Dashboard">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                <i class="fas fa-chart-line text-gray-600 dark:text-gray-400"></i>
            </div>
            <div>
                <h1 class="page-title dark:page-title-dark">Dashboard Admin</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ringkasan statistik sistem dan log audit aktivitas</p>
            </div>
        </div>
    </x-slot>

    {{-- Stats Cards --}}
    <div class="mb-6 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        {{-- Total Users --}}
        <div class="card dark:card-dark">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Total Pegawai Kontrak</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalPegawai }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/30">
                        <i class="fas fa-users text-xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Staff PSDM --}}
        <div class="card dark:card-dark">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Staff PSDM (SDM)</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalPsdm }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/30">
                        <i class="fas fa-user-shield text-xl text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Staff Keuangan --}}
        <div class="card dark:card-dark">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Staff Keuangan</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalKeuangan }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 dark:bg-green-900/30">
                        <i class="fas fa-wallet text-xl text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Admins --}}
        <div class="card dark:card-dark">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Administrator</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalAdmins }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 dark:bg-purple-900/30">
                        <i class="fas fa-user-cog text-xl text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- System and Activities Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {{-- System Configurations Panel --}}
        <div class="lg:col-span-5 space-y-6">
            <div class="card dark:card-dark">
                <div class="card-header dark:card-header-dark">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-sliders-h text-gray-500"></i>
                        Konfigurasi Aktif Sistem
                    </h3>
                </div>
                <div class="card-body space-y-4">
                    {{-- Geofencing Settings --}}
                    <div>
                        <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Lokasi & Radius Presensi</h4>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Latitude Kantor</span>
                                <span class="font-mono text-sm font-semibold text-gray-900 dark:text-white">{{ $settings['office_latitude'] ?? 'Belum diatur' }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Longitude Kantor</span>
                                <span class="font-mono text-sm font-semibold text-gray-900 dark:text-white">{{ $settings['office_longitude'] ?? 'Belum diatur' }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Radius Absensi</span>
                                <span class="badge badge-success">{{ $settings['allowed_radius_meters'] ?? '100' }} meter</span>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-200 dark:border-slate-700" />

                    {{-- Shifts Configuration --}}
                    <div>
                        <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Konfigurasi Shift Kerja</h4>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Jumlah Shift Aktif</span>
                            <span class="badge badge-info">{{ $shiftsCount }} Shift</span>
                        </div>
                    </div>

                    <a href="{{ route('admin.settings') }}" class="btn btn-secondary w-full justify-center mt-2">
                        <i class="fas fa-cog mr-1.5"></i>
                        Buka Pengaturan Sistem
                    </a>
                </div>
            </div>
            
            {{-- Quick Shortcuts --}}
            <div class="card dark:card-dark">
                <div class="card-header dark:card-header-dark">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-bolt text-amber-500"></i>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="card-body grid grid-cols-2 gap-3">
                    <a href="{{ route('admin.staffs.create') }}" class="p-3 text-center border border-gray-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-400 rounded-xl bg-gray-50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-800 transition-all group">
                        <i class="fas fa-user-plus text-xl text-blue-500 group-hover:scale-110 transition-transform mb-2 block"></i>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Tambah Staff</span>
                    </a>
                    <a href="{{ route('admin.staffs') }}" class="p-3 text-center border border-gray-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-400 rounded-xl bg-gray-50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-800 transition-all group">
                        <i class="fas fa-users-cog text-xl text-indigo-500 group-hover:scale-110 transition-transform mb-2 block"></i>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Kelola Staff</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Activity Logs Audit Trail --}}
        <div class="lg:col-span-7">
            <div class="card dark:card-dark h-full">
                <div class="card-header dark:card-header-dark flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-history text-indigo-500"></i>
                        Log Audit Aktivitas Sistem
                    </h3>
                    <a href="{{ route('admin.activity-logs') }}" class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium">Lihat Semua &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pelaku</th>
                                <th>Aksi & Deskripsi</th>
                                <th>Modul</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activityLogs as $log)
                                <tr>
                                    <td class="text-xs text-gray-500 whitespace-nowrap">
                                        {{ $log->created_at->format('d M Y H:i') }} WIB
                                    </td>
                                    <td>
                                        <div class="font-medium text-gray-900 dark:text-white text-xs">{{ $log->user->name ?? '-' }}</div>
                                        <div class="text-[10px] text-gray-500 font-mono">{{ $log->ip_address }}</div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1.5 mb-0.5">
                                            @if($log->action === 'create')
                                                <span class="badge badge-success text-[10px] px-1 py-0.5">Create</span>
                                            @elseif($log->action === 'update')
                                                <span class="badge badge-info text-[10px] px-1 py-0.5">Update</span>
                                            @elseif($log->action === 'delete')
                                                <span class="badge badge-danger text-[10px] px-1 py-0.5">Delete</span>
                                            @else
                                                <span class="badge badge-secondary text-[10px] px-1 py-0.5">{{ $log->action }}</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 max-w-xs break-words">{{ $log->description }}</p>
                                    </td>
                                    <td class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $log->model_type ?? 'Sistem' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-gray-500 dark:text-slate-400 py-10">
                                        <i class="fas fa-shield-alt text-4xl mb-3 opacity-30 text-gray-400"></i>
                                        <p class="text-sm">Belum ada riwayat aktivitas tercatat</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
