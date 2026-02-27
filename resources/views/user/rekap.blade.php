<x-app-layout title="Rekap Absensi">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                <i class="fas fa-chart-bar text-purple-600 dark:text-purple-400"></i>
            </div>
            <div>
                <h1 class="page-title">Rekap Absensi</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
                </p>
            </div>
        </div>
    </x-slot>

    {{-- Filter --}}
    <div class="card mb-6">
        <div class="card-body">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="form-field mb-0">
                        <label class="form-label">Bulan</label>
                        <select name="month" class="form-control" onchange="this.form.submit()">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-field mb-0">
                        <label class="form-label">Tahun</label>
                        <select name="year" class="form-control" onchange="this.form.submit()">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Download Section --}}
    <div class="card mb-6" x-data="{ filterType: 'month' }">
        <div class="card-header">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-download text-green-500 mr-2"></i>
                Download Rekap Absensi
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('user.rekap.export') }}" class="space-y-4">
                {{-- Filter Type Selector --}}
                <div class="flex flex-wrap items-center gap-3">
                    <label class="form-label mb-0 mr-2">Tipe Download:</label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer px-3 py-1.5 rounded-lg border transition-colors"
                           :class="filterType === 'day' ? 'bg-emerald-50 border-emerald-300 text-emerald-700 dark:bg-emerald-900/30 dark:border-emerald-600 dark:text-emerald-400' : 'border-gray-200 dark:border-slate-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-700'">
                        <input type="radio" name="filter_type" value="day" x-model="filterType" class="hidden">
                        <i class="fas fa-calendar-day text-xs"></i>
                        <span class="text-sm font-medium">Per Hari</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer px-3 py-1.5 rounded-lg border transition-colors"
                           :class="filterType === 'month' ? 'bg-emerald-50 border-emerald-300 text-emerald-700 dark:bg-emerald-900/30 dark:border-emerald-600 dark:text-emerald-400' : 'border-gray-200 dark:border-slate-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-700'">
                        <input type="radio" name="filter_type" value="month" x-model="filterType" class="hidden">
                        <i class="fas fa-calendar-alt text-xs"></i>
                        <span class="text-sm font-medium">Per Bulan</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer px-3 py-1.5 rounded-lg border transition-colors"
                           :class="filterType === 'all' ? 'bg-emerald-50 border-emerald-300 text-emerald-700 dark:bg-emerald-900/30 dark:border-emerald-600 dark:text-emerald-400' : 'border-gray-200 dark:border-slate-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-700'">
                        <input type="radio" name="filter_type" value="all" x-model="filterType" class="hidden">
                        <i class="fas fa-layer-group text-xs"></i>
                        <span class="text-sm font-medium">Semua Data</span>
                    </label>
                </div>

                {{-- Dynamic Filter Fields --}}
                <div class="flex flex-wrap items-end gap-4">
                    {{-- Per Hari --}}
                    <div class="form-group mb-0" x-show="filterType === 'day'" x-cloak>
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" value="{{ now()->toDateString() }}" class="form-control">
                    </div>

                    {{-- Per Bulan --}}
                    <div class="form-group mb-0" x-show="filterType === 'month'" x-cloak>
                        <label class="form-label">Bulan</label>
                        <select name="month" class="form-control">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group mb-0" x-show="filterType === 'month'" x-cloak>
                        <label class="form-label">Tahun</label>
                        <select name="year" class="form-control">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Semua --}}
                    <div x-show="filterType === 'all'" x-cloak>
                        <p class="text-sm text-gray-500 dark:text-gray-400 py-2">
                            <i class="fas fa-info-circle mr-1"></i> Semua data absensi Anda akan didownload
                        </p>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-excel mr-1"></i> Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-3 mb-6">
        <div class="stat-card">
            <div>
                <p class="stat-card-label">Total Hadir</p>
                <p class="stat-card-value">{{ $stats['total_hadir'] }}</p>
            </div>
            <div class="stat-card-icon bg-blue-100 dark:bg-blue-900">
                <i class="fas fa-calendar-check text-xl text-blue-600 dark:text-blue-400"></i>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <p class="stat-card-label">Tepat Waktu</p>
                <p class="stat-card-value">{{ $stats['total_tepat_waktu'] }}</p>
            </div>
            <div class="stat-card-icon bg-emerald-100 dark:bg-emerald-900">
                <i class="fas fa-check-circle text-xl text-emerald-600 dark:text-emerald-400"></i>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <p class="stat-card-label">Terlambat</p>
                <p class="stat-card-value">{{ $stats['total_terlambat'] }}</p>
            </div>
            <div class="stat-card-icon bg-amber-100 dark:bg-amber-900">
                <i class="fas fa-exclamation-circle text-xl text-amber-600 dark:text-amber-400"></i>
            </div>
        </div>
    </div>

    {{-- Attendance List --}}
    <div class="card" x-data="{ showModal: false, photoUrl: '', photoTitle: '' }">
        <div class="card-header flex items-center justify-between">
            <h3 class="card-title flex items-center gap-2">
                <i class="fas fa-list-alt text-purple-500 text-sm"></i>
                Riwayat Absensi
            </h3>
            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $attendances->count() }} data</span>
        </div>
        <div class="card-body p-0">
            <div class="divide-y divide-gray-100 dark:divide-slate-700">
                @forelse($attendances as $attendance)
                    <div class="px-5 py-4 hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            {{-- Left: Date, Shift & Times --}}
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <div class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0 mt-0.5
                                    @if($attendance->status === 'left') bg-rose-50 dark:bg-rose-900/20
                                    @elseif($attendance->status === 'late') bg-amber-50 dark:bg-amber-900/20
                                    @elseif($attendance->status === 'cuti') bg-indigo-50 dark:bg-indigo-900/20
                                    @elseif($attendance->status === 'dinas_luar') bg-blue-50 dark:bg-blue-900/20
                                    @else bg-emerald-50 dark:bg-emerald-900/20 @endif">
                                    <i class="fas {{ in_array($attendance->status, ['cuti', 'dinas_luar']) ? ($attendance->status === 'dinas_luar' ? 'fa-briefcase' : 'fa-calendar-minus') : 'fa-calendar-check' }} text-sm 
                                        @if($attendance->status === 'left') text-rose-500 dark:text-rose-400
                                        @elseif($attendance->status === 'late') text-amber-500 dark:text-amber-400
                                        @elseif($attendance->status === 'cuti') text-indigo-500 dark:text-indigo-400
                                        @elseif($attendance->status === 'dinas_luar') text-blue-500 dark:text-blue-400
                                        @else text-emerald-500 dark:text-emerald-400 @endif"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $attendance->check_in_time->translatedFormat('l, d M Y') }}
                                        </p>
                                        @if($attendance->shift)
                                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 dark:bg-slate-600 text-gray-500 dark:text-gray-300 font-medium">
                                                {{ $attendance->shift->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-4 mt-1.5 flex-wrap">
                                        @if($attendance->status === 'cuti')
                                            <span class="inline-flex items-center gap-1 text-xs text-indigo-500 dark:text-indigo-400">
                                                <i class="fas fa-calendar-minus text-[10px]"></i>
                                                Hari Cuti
                                            </span>
                                        @elseif($attendance->status === 'dinas_luar')
                                            <span class="inline-flex items-center gap-1 text-xs text-blue-500 dark:text-blue-400">
                                                <i class="fas fa-briefcase text-[10px]"></i>
                                                Dinas Luar
                                            </span>
                                        @else
                                        {{-- Check-in --}}
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1 text-xs">
                                                <i class="fas fa-sign-in-alt text-[10px] text-emerald-500"></i>
                                                <span class="badge badge-success text-xs">{{ $attendance->check_in_time->format('H:i') }}</span>
                                            </span>
                                            @if($attendance->photo_path && !in_array($attendance->photo_path, ['cuti', 'dinas_luar']))
                                                <button @click="showModal = true; photoUrl = '{{ asset('storage/' . $attendance->photo_path) }}'; photoTitle = 'Foto Masuk - {{ $attendance->check_in_time->format('d M Y') }}'" 
                                                        class="inline-flex items-center gap-1 text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40 transition-colors cursor-pointer"
                                                        title="Foto Masuk">
                                                    <i class="fas fa-camera text-[8px]"></i> Foto
                                                </button>
                                            @endif
                                        </div>
                                        {{-- Check-out --}}
                                        <div class="flex items-center gap-2">
                                            @if($attendance->check_out_time)
                                                <span class="inline-flex items-center gap-1 text-xs">
                                                    <i class="fas fa-sign-out-alt text-[10px] text-blue-500"></i>
                                                    <span class="badge badge-info text-xs">{{ $attendance->check_out_time->format('H:i') }}</span>
                                                </span>
                                                @if($attendance->check_out_photo_path)
                                                    <button @click="showModal = true; photoUrl = '{{ asset('storage/' . $attendance->check_out_photo_path) }}'; photoTitle = 'Foto Pulang - {{ $attendance->check_out_time->format('d M Y') }}'" 
                                                            class="inline-flex items-center gap-1 text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition-colors cursor-pointer"
                                                            title="Foto Pulang">
                                                        <i class="fas fa-camera text-[8px]"></i> Foto
                                                    </button>
                                                @endif
                                            @else
                                                <span class="text-xs text-gray-400">
                                                    <i class="fas fa-sign-out-alt text-[10px] mr-1"></i> —
                                                </span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Status --}}
                            <div class="shrink-0">
                                @if($attendance->status === 'late')
                                    <span class="badge badge-warning">Terlambat</span>
                                @elseif($attendance->status === 'left')
                                    <span class="badge badge-danger">Meninggalkan Kantor</span>
                                @elseif($attendance->status === 'cuti')
                                    <span class="badge" style="background-color: rgb(238 242 255); color: rgb(67 56 202);">Cuti</span>
                                @elseif($attendance->status === 'dinas_luar')
                                    <span class="badge" style="background-color: rgb(219 234 254); color: rgb(29 78 216);">Dinas Luar</span>
                                @else
                                    <span class="badge badge-success">Hadir</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center">
                        <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-700 mb-3">
                            <i class="fas fa-calendar-times text-2xl text-gray-400 dark:text-gray-500"></i>
                        </div>
                        <p class="font-medium text-gray-900 dark:text-white">Tidak ada data absensi</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Belum ada riwayat untuk periode ini</p>
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
