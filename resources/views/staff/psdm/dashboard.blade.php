<x-app-layout title="Dashboard PSDM">
    <x-slot name="header">
        <h1 class="page-title dark:page-title-dark">Dashboard PSDM</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Overview performa karyawan hari ini</p>
    </x-slot>

    {{-- Stats Grid --}}
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        {{-- Total Pegawai --}}
        <div class="card dark:card-dark relative overflow-hidden">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pegawai</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalUsers ?? 0 }}</h3>
                    </div>
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hadir Hari Ini --}}
        <div class="card dark:card-dark relative overflow-hidden">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Hadir Hari Ini</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $todayAttendances ?? 0 }}</h3>
                    </div>
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-user-check text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tepat Waktu --}}
        <div class="card dark:card-dark relative overflow-hidden">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tepat Waktu</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $todayOnTime ?? 0 }}</h3>
                    </div>
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg text-indigo-600 dark:text-indigo-400">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Terlambat --}}
        <div class="card dark:card-dark relative overflow-hidden">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Terlambat</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $todayLate ?? 0 }}</h3>
                    </div>
                    <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg text-red-600 dark:text-red-400">
                        <i class="fas fa-exclamation-circle text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Recent Attendance --}}
        <div class="lg:col-span-2">
            <div class="card dark:card-dark h-full">
                <div class="card-header dark:card-header-dark flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Absensi Terbaru</h3>
                    <a href="{{ route('staff.psdm.monitor') }}" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table dark:table-dark w-full text-sm">
                        <thead>
                            <tr>
                                <th>Pegawai</th>
                                <th>Waktu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @forelse($recentAttendances ?? [] as $attendance)
                            <tr>
                                <td class="py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-xs bg-gray-200">
                                            {{ substr($attendance->user->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $attendance->user->name ?? 'Unknown' }}</p>
                                            <p class="text-xs text-gray-500">{{ $attendance->shift->name ?? 'Reguler' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-gray-600 dark:text-gray-300">
                                    @if($attendance->status == 'cuti')
                                        <span class="text-indigo-500 dark:text-indigo-400">—</span>
                                    @else
                                        {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->status == 'late')
                                        <span class="badge badge-danger text-xs">Terlambat</span>
                                    @elseif($attendance->status == 'left')
                                        <span class="badge badge-danger text-xs">Meninggalkan Kantor</span>
                                    @elseif($attendance->status == 'cuti')
                                        <span class="badge text-xs" style="background-color: rgb(238 242 255); color: rgb(67 56 202);">Cuti</span>
                                    @else
                                        <span class="badge badge-success text-xs">Tepat Waktu</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-6 text-gray-500">Belum ada absensi hari ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Actions & Announcements --}}
        <div class="space-y-6">
            {{-- Quick Actions --}}
            <div class="card dark:card-dark">
                <div class="card-header dark:card-header-dark">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Aksi Cepat</h3>
                </div>
                <div class="card-body grid grid-cols-2 gap-4">
                    <a href="{{ route('staff.psdm.users.create') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors group">
                        <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-plus text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Tambah Pegawai</span>
                    </a>
                    <a href="{{ route('staff.psdm.announcements.create') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors group">
                        <div class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-bullhorn text-red-600 dark:text-red-400"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Buat Pengumuman</span>
                    </a>
                    <a href="{{ route('staff.psdm.monitor') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors group">
                        <div class="h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-desktop text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Monitor Absensi</span>
                    </a>
                    <a href="{{ route('staff.psdm.master-data') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-slate-700/50 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors group">
                        <div class="h-10 w-10 rounded-full bg-violet-100 dark:bg-violet-900 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-database text-violet-600 dark:text-violet-400"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Master Data</span>
                    </a>
                </div>
            </div>

            {{-- Announcements --}}
            <div class="card dark:card-dark">
                <div class="card-header dark:card-header-dark flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Pengumuman Aktif</h3>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        @forelse($announcements ?? [] as $announcement)
                        <div class="pb-3 border-b dark:border-slate-700 last:border-0 last:pb-0">
                            <p class="font-medium text-gray-900 dark:text-white line-clamp-1">{{ $announcement->title }}</p>
                            <p class="text-xs text-gray-500 mb-1">{{ $announcement->created_at->diffForHumans() }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                {{ Str::limit($announcement->content, 80) }}
                            </p>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 text-center py-4">Tidak ada pengumuman aktif.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
