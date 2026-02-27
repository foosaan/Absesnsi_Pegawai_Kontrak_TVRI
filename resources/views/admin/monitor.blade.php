<x-app-layout title="Monitor Absensi">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900">
                    <i class="fas fa-desktop text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Monitor Absensi</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Pantau kehadiran karyawan secara real-time (read-only)
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Filter --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.monitor') }}" id="monitorForm" class="space-y-4">
                {{-- Row 1: Search & Quick Filters --}}
                <div class="flex flex-wrap items-end gap-4">
                    <div class="form-group mb-0 flex-1 min-w-[200px]">
                        <label class="form-label">Cari Karyawan</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Nama atau NIP..." 
                                   class="form-control pl-9">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Tanggal Spesifik</label>
                        <input type="date" name="date" value="{{ request('date') }}" 
                               class="form-control" onchange="clearMonthYear(); this.form.submit()">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Bulan</label>
                        <select name="month" class="form-control" onchange="clearDate(); this.form.submit()">
                            <option value="">Semua</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Tahun</label>
                        <select name="year" class="form-control" onchange="clearDate(); this.form.submit()">
                            <option value="">Semua</option>
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="on_time" {{ request('status') == 'on_time' ? 'selected' : '' }}>Tepat Waktu</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="left" {{ request('status') == 'left' ? 'selected' : '' }}>Meninggalkan Kantor</option>
                            <option value="cuti" {{ request('status') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                        @if(request()->hasAny(['search', 'date', 'month', 'year', 'status']))
                            <a href="{{ route('admin.monitor') }}" class="btn btn-secondary" title="Reset Filter">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Active Filters Summary --}}
                @if(request()->hasAny(['search', 'date', 'month', 'year', 'status']))
                <div class="flex items-center gap-2 flex-wrap text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Filter aktif:</span>
                    @if(request('search'))
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            <i class="fas fa-user text-[9px]"></i> "{{ request('search') }}"
                        </span>
                    @endif
                    @if(request('date'))
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <i class="fas fa-calendar-day text-[9px]"></i> {{ \Carbon\Carbon::parse(request('date'))->format('d M Y') }}
                        </span>
                    @endif
                    @if(request('month'))
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            <i class="fas fa-calendar text-[9px]"></i> {{ DateTime::createFromFormat('!m', request('month'))->format('F') }}
                        </span>
                    @endif
                    @if(request('year'))
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                            <i class="fas fa-calendar-alt text-[9px]"></i> {{ request('year') }}
                        </span>
                    @endif
                    @if(request('status'))
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <i class="fas fa-filter text-[9px]"></i> {{ request('status') == 'on_time' ? 'Tepat Waktu' : (request('status') == 'late' ? 'Terlambat' : (request('status') == 'left' ? 'Meninggalkan Kantor' : 'Cuti')) }}
                        </span>
                    @endif
                    <span class="text-gray-400 dark:text-gray-500">&bull; {{ $attendances->total() }} data</span>
                </div>
                @endif
            </form>
        </div>
    </div>

    <script>
        function clearDate() {
            document.querySelector('input[name="date"]').value = '';
        }
        function clearMonthYear() {
            document.querySelector('select[name="month"]').value = '';
            document.querySelector('select[name="year"]').value = '';
        }
    </script>

    {{-- Attendance Table --}}
    <div class="card" x-data="{ showModal: false, photoUrl: '', photoTitle: '' }">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Foto</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="dark:text-gray-300">
                    @forelse($attendances ?? [] as $attendance)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm bg-blue-600 text-white">
                                    {{ strtoupper(substr($attendance->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $attendance->user->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $attendance->user->nip ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($attendance->check_in_time)->translatedFormat('d M Y') }}</span>
                            <p class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($attendance->check_in_time)->translatedFormat('l') }}</p>
                        </td>
                        <td>{{ $attendance->shift->name ?? 'Reguler' }}</td>
                        <td>
                            @if($attendance->status == 'cuti')
                                <span class="text-indigo-500 dark:text-indigo-400 font-medium">—</span>
                            @elseif($attendance->check_in_time)
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium">
                                    {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->status == 'cuti')
                                <span class="text-indigo-500 dark:text-indigo-400 font-medium">—</span>
                            @elseif($attendance->check_out_time)
                                <span class="text-blue-600 dark:text-blue-400 font-medium">
                                    {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->status != 'cuti')
                                <div class="flex items-center gap-1.5">
                                    @if($attendance->photo_path && $attendance->photo_path !== 'cuti')
                                        <button @click="showModal = true; photoUrl = '{{ asset('storage/' . $attendance->photo_path) }}'; photoTitle = '{{ ($attendance->user->name ?? '') }} - Foto Masuk {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('d M Y') }}'"
                                                class="h-8 w-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 flex items-center justify-center transition-colors group"
                                                title="Foto Masuk">
                                            <i class="fas fa-camera text-[11px] text-emerald-500 group-hover:text-emerald-600 dark:text-emerald-400"></i>
                                        </button>
                                    @endif
                                    @if($attendance->check_out_photo_path)
                                        <button @click="showModal = true; photoUrl = '{{ asset('storage/' . $attendance->check_out_photo_path) }}'; photoTitle = '{{ ($attendance->user->name ?? '') }} - Foto Pulang {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('d M Y') }}'"
                                                class="h-8 w-8 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 flex items-center justify-center transition-colors group"
                                                title="Foto Pulang">
                                            <i class="fas fa-camera text-[11px] text-blue-500 group-hover:text-blue-600 dark:text-blue-400"></i>
                                        </button>
                                    @endif
                                    @if(!$attendance->photo_path && !$attendance->check_out_photo_path)
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->status == 'late')
                                <span class="badge badge-danger">Terlambat</span>
                            @elseif($attendance->status == 'left')
                                <span class="badge badge-danger">Meninggalkan Kantor</span>
                            @elseif($attendance->status == 'cuti')
                                <span class="badge" style="background-color: rgb(238 242 255); color: rgb(67 56 202);">Cuti</span>
                            @else
                                <span class="badge badge-success">Tepat Waktu</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-calendar-times text-4xl mb-3 opacity-50"></i>
                            <p>Tidak ada data absensi yang ditemukan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($attendances) && $attendances->hasPages())
        <div class="card-body border-t dark:border-slate-700">
            {{ $attendances->links() }}
        </div>
        @endif

        {{-- Photo Modal --}}
        <template x-if="showModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showModal = false">
                <div class="fixed inset-0 bg-black/60" @click="showModal = false"></div>
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden z-10">
                    <div class="flex items-center justify-between px-5 py-3 border-b dark:border-slate-700">
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm" x-text="photoTitle"></h3>
                        <button @click="showModal = false" class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors">
                            <i class="fas fa-times text-gray-400"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <img :src="photoUrl" alt="Foto Absensi" class="w-full rounded-lg shadow-sm">
                    </div>
                </div>
            </div>
        </template>
    </div>

</x-app-layout>
