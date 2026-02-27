<x-app-layout title="Admin Dashboard">
    <x-slot name="header">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard Admin</h1>
    </x-slot>

    {{-- Stats Cards --}}
    <div class="mb-6 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        {{-- Total Users --}}
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Total Pegawai</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $users->count() }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                        <i class="fas fa-users text-xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Today Attendances --}}
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Hadir Hari Ini</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $attendances->where('check_in_time', '>=', now()->startOfDay())->count() }}
                        </p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                        <i class="fas fa-check-circle text-xl text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Late Today --}}
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Terlambat</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $attendances->where('status', 'late')->count() }}
                        </p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <i class="fas fa-clock text-xl text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Shifts --}}
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Shift Aktif</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $shifts->count() }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                        <i class="fas fa-calendar-alt text-xl text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Attendance Table --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">Data Absensi</h3>
        </div>
        <div class="card-body">
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.dashboard') }}" class="mb-6 flex flex-wrap gap-4">
                <div class="form-field mb-0">
                    <label for="filter_date" class="form-label">Tanggal</label>
                    <input type="date" name="filter_date" id="filter_date" value="{{ request('filter_date') }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="form-field mb-0">
                    <label for="filter_user" class="form-label">Pegawai</label>
                    <select name="filter_user" id="filter_user" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Pegawai</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('filter_user') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </form>

            {{-- Attendance Table --}}
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pegawai</th>
                            <th>Tanggal</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Shift</th>
                            <th>Status</th>
                            <th>Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $attendance->user->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-500">{{ $attendance->user->nip ?? '' }}</div>
                                </td>
                                <td>{{ $attendance->check_in_time ? $attendance->check_in_time->format('d M Y') : '-' }}</td>
                                <td>{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}</td>
                                <td>{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}</td>
                                <td>{{ $attendance->shift->name ?? '-' }}</td>
                                <td>
                                    @if($attendance->status == 'present')
                                        <span class="badge badge-success">Hadir</span>
                                    @elseif($attendance->status == 'late')
                                        <span class="badge badge-warning">Terlambat</span>
                                    @elseif($attendance->status == 'left')
                                        <span class="badge badge-danger">Meninggalkan Kantor</span>
                                    @elseif($attendance->status == 'cuti')
                                        <span class="badge" style="background-color: rgb(238 242 255); color: rgb(67 56 202);">Cuti</span>
                                    @elseif($attendance->status == 'absent')
                                        <span class="badge badge-danger">Tidak Hadir</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $attendance->status ?? '-' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->check_in_photo)
                                        <img src="{{ asset('storage/' . $attendance->check_in_photo) }}" alt="Check In" class="h-10 w-10 rounded-lg object-cover">
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-500 dark:text-slate-400">
                                    Tidak ada data absensi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Shift Logs --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Log Perubahan Shift</h3>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Shift</th>
                            <th>Diubah Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shiftLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $log->shift->name ?? '-' }}</td>
                                <td>{{ $log->changedByUser->name ?? '-' }}</td>
                                <td>{{ $log->action ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-gray-500 dark:text-slate-400">
                                    Tidak ada log perubahan shift
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
