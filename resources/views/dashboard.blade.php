<x-app-layout title="Dashboard">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h1 class="page-title dark:page-title-dark">Dashboard</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Selamat datang, {{ auth()->user()->name }}</p>
        </div>
    </x-slot>

    {{-- Profil Biodata --}}
    <div class="card mb-6">
        <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-id-card text-blue-500"></i>
                Profil Biodata
            </h3>
        </div>
        <div class="card-body">
            <div class="flex flex-col md:flex-row gap-8">
                {{-- Photo --}}
                <div class="flex flex-col items-center md:items-start shrink-0">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" 
                             alt="Foto Profil" 
                             class="h-24 w-24 md:h-28 md:w-28 rounded-full object-cover ring-4 ring-blue-50 dark:ring-blue-900/50 shadow-lg">
                    @else
                        <div class="h-24 w-24 md:h-28 md:w-28 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center ring-4 ring-blue-50 dark:ring-blue-900/50 shadow-lg">
                            <span class="text-3xl md:text-4xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    <div class="mt-3 text-center md:text-left">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                        <span class="inline-block mt-1.5 badge badge-primary text-xs">{{ auth()->user()->status_pegawai ?? '-' }}</span>
                    </div>
                </div>

                {{-- Biodata Grid --}}
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="bg-gray-50 dark:bg-slate-700/40 rounded-lg px-4 py-3">
                        <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest">NIP</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ auth()->user()->nip ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-slate-700/40 rounded-lg px-4 py-3">
                        <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest">NIK</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ auth()->user()->nik ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-slate-700/40 rounded-lg px-4 py-3">
                        <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Jabatan</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ auth()->user()->jabatan ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-slate-700/40 rounded-lg px-4 py-3">
                        <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Bagian</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ auth()->user()->bagian ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-slate-700/40 rounded-lg px-4 py-3">
                        <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Jenis Kelamin</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ auth()->user()->jenis_kelamin ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-slate-700/40 rounded-lg px-4 py-3 sm:col-span-2 lg:col-span-3">
                        <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Alamat</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ auth()->user()->alamat ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Row --}}
    <div class="grid gap-4 lg:grid-cols-3 mb-6">
        {{-- Status Card --}}
        <div class="card">
            <div class="card-body flex items-center">
                <div class="flex items-center gap-4 w-full">
                    @if($todayAttendance)
                        <div class="h-12 w-12 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center shrink-0">
                            <i class="fas fa-check-circle text-xl text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Status Absensi</p>
                            @if($todayAttendance->check_out_time)
                                <h3 class="text-base font-bold text-emerald-600 dark:text-emerald-400">Sudah Pulang</h3>
                                <p class="text-[11px] text-gray-400 mt-0.5">
                                    {{ $todayAttendance->check_in_time->format('H:i') }} – {{ $todayAttendance->check_out_time->format('H:i') }}
                                    · {{ $todayAttendance->check_in_time->diffForHumans($todayAttendance->check_out_time, true) }}
                                </p>
                            @else
                                <h3 class="text-base font-bold text-emerald-600 dark:text-emerald-400">Sudah Check-in</h3>
                                <p class="text-[11px] text-gray-400 mt-0.5">Masuk: {{ $todayAttendance->check_in_time->format('H:i') }}</p>
                            @endif
                        </div>
                    @else
                        <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center shrink-0">
                            <i class="fas fa-clock text-xl text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Status Absensi</p>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Belum Check-in</h3>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Date Card --}}
        <div class="card">
            <div class="card-body flex items-center">
                <div class="flex items-center gap-4 w-full">
                    <div class="h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center shrink-0">
                        <i class="fas fa-calendar-day text-xl text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Hari Ini</p>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ now()->translatedFormat('l, d M Y') }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Quick Action --}}
        <div class="card">
            <div class="card-body flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Absensi Cepat</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Catat kehadiran anda</p>
                </div>
                <a href="{{ route('attendance.index') }}" class="btn btn-primary flex items-center gap-2">
                    <i class="fas fa-camera"></i> <span class="hidden sm:inline">Absen</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Shift Info Card --}}
    @if(auth()->user()->isShiftAttendance() && $allShifts)
    <div class="card mb-6 border-l-4 border-purple-500">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-bold text-purple-700 dark:text-purple-400 flex items-center gap-2">
                    <i class="fas fa-shield-alt"></i> Jadwal Shift Anda
                </h4>
                @if($currentShift)
                    <span class="badge badge-primary text-xs">
                        <i class="fas fa-clock mr-1"></i> Sekarang: {{ $currentShift->name }}
                    </span>
                @else
                    <span class="badge badge-secondary text-xs">
                        <i class="fas fa-moon mr-1"></i> Di luar jam shift
                    </span>
                @endif
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach($allShifts as $shift)
                    <div class="p-3 rounded-lg border transition-all {{ $currentShift && $currentShift->id === $shift->id 
                        ? 'bg-purple-50 dark:bg-purple-900/30 border-purple-300 dark:border-purple-600 ring-2 ring-purple-200 dark:ring-purple-800' 
                        : 'bg-gray-50 dark:bg-slate-700/50 border-gray-200 dark:border-slate-600' }}">
                        <div class="flex items-center justify-between mb-1">
                            <p class="font-bold text-sm {{ $currentShift && $currentShift->id === $shift->id ? 'text-purple-700 dark:text-purple-300' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $shift->name }}
                            </p>
                            @if($currentShift && $currentShift->id === $shift->id)
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-purple-200 text-purple-700 dark:bg-purple-800 dark:text-purple-300 font-semibold">AKTIF</span>
                            @endif
                        </div>
                        <p class="text-sm font-mono {{ $currentShift && $currentShift->id === $shift->id ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500 dark:text-gray-400' }}">
                            <i class="fas fa-clock text-[10px] mr-1"></i>
                            {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @elseif(auth()->user()->isNormalAttendance() && $currentShift)
    <div class="card mb-6 border-l-4 border-emerald-500">
        <div class="card-body">
            <h4 class="font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-2 mb-3">
                <i class="fas fa-calendar-alt"></i> Jadwal Kerja Anda
            </h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg px-4 py-3">
                    <p class="text-[10px] font-semibold text-emerald-500 dark:text-emerald-500 uppercase tracking-widest">Jam Masuk</p>
                    <p class="text-lg font-bold font-mono text-emerald-700 dark:text-emerald-300 mt-0.5">{{ \Carbon\Carbon::parse($currentShift->start_time)->format('H:i') }}</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg px-4 py-3">
                    <p class="text-[10px] font-semibold text-blue-500 dark:text-blue-500 uppercase tracking-widest">Jam Pulang</p>
                    <p class="text-lg font-bold font-mono text-blue-700 dark:text-blue-300 mt-0.5">{{ \Carbon\Carbon::parse($currentShift->end_time)->format('H:i') }}</p>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg px-4 py-3">
                    <p class="text-[10px] font-semibold text-amber-500 dark:text-amber-500 uppercase tracking-widest">Toleransi</p>
                    <p class="text-lg font-bold font-mono text-amber-700 dark:text-amber-300 mt-0.5">{{ $currentShift->tolerance_minutes }} <span class="text-xs font-normal">menit</span></p>
                </div>
                <div class="bg-gray-50 dark:bg-slate-700/50 rounded-lg px-4 py-3">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Durasi</p>
                    <p class="text-lg font-bold font-mono text-gray-700 dark:text-gray-300 mt-0.5">8 <span class="text-xs font-normal">jam</span></p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Stats Bulan Ini --}}
    <div class="grid gap-4 sm:grid-cols-3 mb-6">
        <div class="card p-5 flex flex-row items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-slate-400">Total Hadir</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_hadir'] ?? 0 }}</p>
            </div>
            <div class="h-11 w-11 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center shrink-0">
                <i class="fas fa-calendar-check text-lg text-blue-600 dark:text-blue-400"></i>
            </div>
        </div>
        <div class="card p-5 flex flex-row items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-slate-400">Tepat Waktu</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_tepat_waktu'] ?? 0 }}</p>
            </div>
            <div class="h-11 w-11 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center shrink-0">
                <i class="fas fa-check-circle text-lg text-emerald-600 dark:text-emerald-400"></i>
            </div>
        </div>
        <div class="card p-5 flex flex-row items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-slate-400">Terlambat</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_terlambat'] ?? 0 }}</p>
            </div>
            <div class="h-11 w-11 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center shrink-0">
                <i class="fas fa-exclamation-circle text-lg text-amber-600 dark:text-amber-400"></i>
            </div>
        </div>
    </div>

    {{-- Riwayat Absensi & Pengumuman --}}
    <div class="grid gap-6 lg:grid-cols-5 mb-6" x-data="{ showModal: false, photoUrl: '', photoTitle: '' }">
        {{-- Riwayat Absensi - wider column --}}
        <div class="card h-fit lg:col-span-3">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-history text-blue-500 text-sm"></i>
                    Riwayat Absensi Terakhir
                </h3>
                <a href="{{ route('user.rekap') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 flex items-center gap-1">
                    Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="divide-y divide-gray-100 dark:divide-slate-700">
                    @forelse($recentAttendances as $attendance)
                        <div class="px-5 py-4 hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                {{-- Date & Status --}}
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0
                                        @if($attendance->status == 'left') bg-rose-50 dark:bg-rose-900/20
                                        @elseif($attendance->status == 'late') bg-amber-50 dark:bg-amber-900/20
                                        @elseif($attendance->status == 'cuti') bg-indigo-50 dark:bg-indigo-900/20
                                        @elseif($attendance->status == 'dinas_luar') bg-blue-50 dark:bg-blue-900/20
                                        @else bg-emerald-50 dark:bg-emerald-900/20 @endif">
                                        <i class="fas {{ $attendance->status == 'cuti' ? 'fa-calendar-minus' : ($attendance->status == 'dinas_luar' ? 'fa-briefcase' : 'fa-calendar-check') }} text-sm 
                                            @if($attendance->status == 'left') text-rose-500 dark:text-rose-400
                                            @elseif($attendance->status == 'late') text-amber-500 dark:text-amber-400
                                            @elseif($attendance->status == 'cuti') text-indigo-500 dark:text-indigo-400
                                            @elseif($attendance->status == 'dinas_luar') text-blue-500 dark:text-blue-400
                                            @else text-emerald-500 dark:text-emerald-400 @endif"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $attendance->check_in_time->translatedFormat('l, d M Y') }}
                                        </p>
                                        <div class="flex items-center gap-3 mt-1 flex-wrap">
                                            @if($attendance->status == 'cuti')
                                                <span class="inline-flex items-center gap-1 text-xs text-indigo-500 dark:text-indigo-400">
                                                    <i class="fas fa-calendar-minus text-[10px]"></i>
                                                    Hari Cuti
                                                </span>
                                            @elseif($attendance->status == 'dinas_luar')
                                                <span class="inline-flex items-center gap-1 text-xs text-blue-500 dark:text-blue-400">
                                                    <i class="fas fa-briefcase text-[10px]"></i>
                                                    Dinas Luar
                                                </span>
                                            @else
                                            <span class="inline-flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-sign-in-alt text-[10px]"></i>
                                                {{ $attendance->check_in_time->format('H:i') }}
                                            </span>
                                            @if($attendance->check_out_time)
                                                <span class="inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400">
                                                    <i class="fas fa-sign-out-alt text-[10px]"></i>
                                                    {{ $attendance->check_out_time->format('H:i') }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">— Belum pulang</span>
                                            @endif
                                            @endif
                                            @if($attendance->status == 'late')
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 font-medium">Terlambat</span>
                                            @elseif($attendance->status == 'left')
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 font-medium">Meninggalkan Kantor</span>
                                            @elseif($attendance->status == 'cuti')
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 font-medium">Cuti</span>
                                            @elseif($attendance->status == 'dinas_luar')
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-medium">Dinas Luar</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Photo Buttons --}}
                                @if(!in_array($attendance->status, ['cuti', 'dinas_luar']))
                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if($attendance->photo_path && !in_array($attendance->photo_path, ['cuti', 'dinas_luar']))
                                        <button @click="showModal = true; photoUrl = '{{ asset('storage/' . $attendance->photo_path) }}'; photoTitle = 'Foto Masuk - {{ $attendance->check_in_time->format('d M Y') }}'" 
                                                class="h-8 w-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 flex items-center justify-center transition-colors group"
                                                title="Lihat Foto Masuk">
                                            <i class="fas fa-camera text-[11px] text-emerald-500 group-hover:text-emerald-600 dark:text-emerald-400"></i>
                                        </button>
                                    @endif
                                    @if($attendance->check_out_photo_path)
                                        <button @click="showModal = true; photoUrl = '{{ asset('storage/' . $attendance->check_out_photo_path) }}'; photoTitle = 'Foto Pulang - {{ $attendance->check_out_time->format('d M Y') }}'" 
                                                class="h-8 w-8 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 flex items-center justify-center transition-colors group"
                                                title="Lihat Foto Pulang">
                                            <i class="fas fa-camera text-[11px] text-blue-500 group-hover:text-blue-600 dark:text-blue-400"></i>
                                        </button>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <div class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-700 mb-3">
                                <i class="fas fa-calendar-times text-xl text-gray-400 dark:text-gray-500"></i>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada riwayat absensi</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pengumuman --}}
        <div class="card h-fit lg:col-span-2">
            <div class="card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-bullhorn text-amber-500 text-sm"></i>
                    Pengumuman Terbaru
                </h3>
            </div>
            <div class="card-body">
                @forelse($announcements ?? [] as $announcement)
                    <div class="border-b last:border-0 border-gray-100 dark:border-slate-700 py-4 first:pt-0 last:pb-0">
                        <h4 class="font-semibold text-gray-900 dark:text-white">{{ $announcement->title }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1.5 leading-relaxed">{{ Str::limit($announcement->content, 150) }}</p>
                        <div class="flex items-center gap-1.5 mt-2">
                             <i class="far fa-clock text-[10px] text-gray-400"></i>
                             <p class="text-[11px] text-gray-400">{{ $announcement->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <div class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-800 mb-3">
                            <i class="fas fa-bullhorn text-xl text-gray-400 dark:text-gray-500"></i>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Tidak ada pengumuman</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Belum ada informasi terbaru.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Photo Modal --}}
        <div x-show="showModal" 
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="showModal = false" 
                 class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-lg w-full overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">
                
                <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-slate-700">
                    <h3 class="font-bold text-gray-900 dark:text-white" x-text="photoTitle">Foto Absensi</h3>
                    <button @click="showModal = false" class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors">
                        <i class="fas fa-times text-gray-400"></i>
                    </button>
                </div>
                
                <div class="p-4 flex justify-center bg-gray-50 dark:bg-slate-900/50">
                    <img :src="photoUrl" class="max-h-[70vh] rounded-lg shadow-sm" alt="Foto Absensi">
                </div>
                
                <div class="p-4 border-t border-gray-100 dark:border-slate-700 flex justify-end">
                    <button @click="showModal = false" class="btn btn-secondary">Tutup</button>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
