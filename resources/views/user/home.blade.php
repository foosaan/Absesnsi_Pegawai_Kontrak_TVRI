<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">🏠 Beranda</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-4">
                    
                    <!-- Status Absensi Hari Ini -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-lg mb-4">📅 Absensi Hari Ini</h3>
                        
                        @if($todayAttendance)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-3xl">✅</span>
                                    <div>
                                        <p class="font-bold text-green-700">Sudah Absen Masuk</p>
                                        <p class="text-sm text-gray-600">{{ $todayAttendance->check_in_time->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                                
                                @if($todayAttendance->check_out_time)
                                    <div class="mt-3 pt-3 border-t border-green-200">
                                        <p class="text-sm text-green-600">
                                            🔴 Sudah pulang: {{ $todayAttendance->check_out_time->format('H:i') }}
                                        </p>
                                    </div>
                                @else
                                    <div class="mt-3 pt-3 border-t border-green-200">
                                        <p class="text-sm text-gray-600">
                                            ⏰ Pulang minimal: {{ $todayAttendance->min_check_out_time ? $todayAttendance->min_check_out_time->format('H:i') : '-' }}
                                        </p>
                                        <a href="{{ route('attendance.index') }}" class="inline-block mt-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                                            Absen Pulang →
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">⚠️</span>
                                    <div>
                                        <p class="font-bold text-yellow-700">Belum Absen Hari Ini</p>
                                        <p class="text-sm text-gray-600">Segera lakukan absen masuk</p>
                                    </div>
                                </div>
                                <a href="{{ route('attendance.index') }}" class="inline-block mt-3 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                                    Absen Sekarang →
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-lg mb-4">🚀 Menu Cepat</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <a href="{{ route('attendance.index') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                <span class="text-2xl mb-2">📷</span>
                                <span class="text-sm font-medium text-center">Absensi</span>
                            </a>
                            <a href="{{ route('user.rekap') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                                <span class="text-2xl mb-2">📊</span>
                                <span class="text-sm font-medium text-center">Rekap Absensi</span>
                            </a>
                            <a href="{{ route('user.salary') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                                <span class="text-2xl mb-2">💰</span>
                                <span class="text-sm font-medium text-center">Slip Gaji</span>
                            </a>
                            <a href="{{ route('user.leaves') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition">
                                <span class="text-2xl mb-2">🏖️</span>
                                <span class="text-sm font-medium text-center">Cuti</span>
                            </a>
                        </div>
                    </div>

                    <!-- Statistik Bulan Ini -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-lg mb-4">📈 Statistik Bulan Ini</h3>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="p-4 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ $stats['total_hadir'] }}</div>
                                <div class="text-xs text-gray-500">Total Hadir</div>
                            </div>
                            <div class="p-4 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ $stats['total_tepat_waktu'] }}</div>
                                <div class="text-xs text-gray-500">Tepat Waktu</div>
                            </div>
                            <div class="p-4 bg-yellow-50 rounded-lg">
                                <div class="text-2xl font-bold text-yellow-600">{{ $stats['total_terlambat'] }}</div>
                                <div class="text-xs text-gray-500">Terlambat</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">
                    
                    <!-- Data Kepegawaian -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-lg mb-4">👤 Data Kepegawaian</h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">NIP</span>
                                <span class="font-mono font-medium">{{ Auth::user()->nip ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Status</span>
                                <span class="font-medium">{{ ucfirst(Auth::user()->status_pegawai ?? '-') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tipe</span>
                                <span class="font-medium">{{ strtoupper(Auth::user()->employee_type ?? '-') }}</span>
                            </div>
                            
                            @if(Auth::user()->gaji_pokok)
                            <div class="pt-3 mt-3 border-t">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Gaji Pokok</span>
                                    <span class="font-mono font-bold text-green-600">Rp {{ number_format(Auth::user()->gaji_pokok, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            @endif
                            
                            @if(Auth::user()->nama_bank && Auth::user()->nomor_rekening)
                            <div class="pt-3 mt-3 border-t">
                                <div class="text-gray-500 mb-1">Rekening Bank</div>
                                <div class="font-medium">{{ Auth::user()->nama_bank }}</div>
                                <div class="font-mono text-sm text-gray-600">{{ Auth::user()->nomor_rekening }}</div>
                            </div>
                            @endif
                        </div>
                        
                        @if(!Auth::user()->nip || !Auth::user()->nomor_rekening || !Auth::user()->gaji_pokok)
                        <div class="mt-4 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-700">
                            ⚠️ Data belum lengkap. Hubungi Staff Keuangan untuk melengkapi.
                        </div>
                        @endif
                    </div>
                    
                    <!-- Pengumuman -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-lg mb-4">📢 Pengumuman</h3>
                        
                        @if($announcements->count() > 0)
                            <div class="space-y-3">
                                @foreach($announcements as $announcement)
                                <div class="p-3 bg-blue-50 rounded border-l-4 border-blue-400">
                                    <h4 class="font-bold text-sm text-blue-800">{{ $announcement->title }}</h4>
                                    <p class="text-xs text-gray-600 mt-1">{{ Str::limit($announcement->content, 100) }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $announcement->created_at->diffForHumans() }}</p>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-400 text-center py-4">Tidak ada pengumuman</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
