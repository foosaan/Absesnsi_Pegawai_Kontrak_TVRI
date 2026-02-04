<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Staff PSDM
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Statistik Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="text-3xl font-bold text-blue-600">{{ $totalUsers }}</div>
                    <div class="text-gray-500 text-sm">Total Karyawan</div>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="text-3xl font-bold text-green-600">{{ $todayAttendances }}</div>
                    <div class="text-gray-500 text-sm">Absensi Hari Ini</div>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="text-3xl font-bold text-emerald-600">{{ $todayOnTime }}</div>
                    <div class="text-gray-500 text-sm">Tepat Waktu</div>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="text-3xl font-bold text-yellow-600">{{ $todayLate }}</div>
                    <div class="text-gray-500 text-sm">Terlambat</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quick Actions -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Menu Cepat -->
                    <div class="bg-white rounded-lg shadow p-5">
                        <h3 class="font-bold text-lg mb-4">🚀 Menu Cepat</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <a href="{{ route('staff.psdm.users') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                <span class="text-2xl mb-2">👥</span>
                                <span class="text-sm font-medium text-center">Kelola User</span>
                            </a>
                            <a href="{{ route('staff.psdm.monitor') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                                <span class="text-2xl mb-2">📊</span>
                                <span class="text-sm font-medium text-center">Monitor Absensi</span>
                            </a>
                            <a href="{{ route('staff.psdm.announcements') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                                <span class="text-2xl mb-2">📢</span>
                                <span class="text-sm font-medium text-center">Pengumuman</span>
                            </a>
                            <a href="{{ route('staff.psdm.users.create') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition">
                                <span class="text-2xl mb-2">➕</span>
                                <span class="text-sm font-medium text-center">Tambah User</span>
                            </a>
                        </div>
                    </div>

                    <!-- Absensi Terbaru -->
                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg">📋 Absensi Hari Ini</h3>
                            <a href="{{ route('staff.psdm.monitor') }}" class="text-blue-600 text-sm hover:underline">Lihat Semua →</a>
                        </div>
                        
                        @if($recentAttendances->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold">Nama</th>
                                        <th class="px-3 py-2 text-center font-semibold">Tipe</th>
                                        <th class="px-3 py-2 text-center font-semibold">Masuk</th>
                                        <th class="px-3 py-2 text-center font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($recentAttendances as $attendance)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2">{{ $attendance->user->name }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="px-2 py-0.5 rounded text-xs {{ $attendance->user->employee_type === 'ob' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }}">
                                                {{ strtoupper($attendance->user->employee_type ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-center font-mono">{{ $attendance->check_in_time->format('H:i') }}</td>
                                        <td class="px-3 py-2 text-center">
                                            @if($attendance->status === 'late')
                                                <span class="px-2 py-0.5 rounded text-xs bg-yellow-100 text-yellow-700">Terlambat</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-700">Hadir</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-gray-400 text-center py-8">Belum ada absensi hari ini</p>
                        @endif
                    </div>
                </div>

                <!-- Sidebar - Pengumuman -->
                <div class="space-y-4">
                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg">📢 Pengumuman Aktif</h3>
                            <a href="{{ route('staff.psdm.announcements.create') }}" class="text-blue-600 text-sm hover:underline">+ Baru</a>
                        </div>
                        
                        @if($announcements->count() > 0)
                        <div class="space-y-3">
                            @foreach($announcements as $announcement)
                            <div class="p-3 bg-blue-50 rounded border-l-4 border-blue-400">
                                <h4 class="font-bold text-sm text-blue-800">{{ $announcement->title }}</h4>
                                <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ Str::limit($announcement->content, 80) }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $announcement->created_at->diffForHumans() }}</p>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-gray-400 text-center py-4">Belum ada pengumuman</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
