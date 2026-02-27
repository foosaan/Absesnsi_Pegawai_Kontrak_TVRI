<x-app-layout title="Input Gaji">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900">
                <i class="fas fa-edit text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div>
                <h1 class="page-title dark:page-title-dark">Input Gaji</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Input gaji karyawan per periode</p>
            </div>
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="card dark:card-dark mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('staff.keuangan.users') }}" class="flex flex-wrap gap-3 items-end">
                {{-- Search --}}
                <div class="flex-1" style="min-width: 200px;">
                    <label class="form-label">Cari Karyawan</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control pl-9" 
                               placeholder="Nama atau NIP...">
                    </div>
                </div>
                {{-- Bulan --}}
                <div style="min-width: 150px;">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month((int) $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                {{-- Tahun --}}
                <div style="min-width: 110px;">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-control" onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Progress + Import Excel --}}
    <div class="grid gap-4 grid-cols-1 sm:grid-cols-3 mb-6">
        {{-- Progress --}}
        <div class="card dark:card-dark sm:col-span-2">
            <div class="card-body flex flex-col justify-center">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        <i class="fas fa-chart-pie text-emerald-500 mr-1"></i>
                        Progress — {{ \Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') }} {{ $year }}
                    </span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">
                        {{ $completedCount ?? 0 }} / {{ $totalUsers ?? 0 }} karyawan
                    </span>
                </div>
                <div class="progress">
                    @php $percent = ($totalUsers ?? 0) > 0 ? (($completedCount ?? 0) / $totalUsers) * 100 : 0; @endphp
                    <div class="progress-bar bg-emerald-500" style="width: {{ $percent }}%"></div>
                </div>
                @if($percent == 100)
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">
                        <i class="fas fa-check-circle mr-1"></i> Semua gaji sudah diinput!
                    </p>
                @elseif(($pendingCount ?? ($totalUsers - $completedCount)) > 0)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        {{ ($totalUsers ?? 0) - ($completedCount ?? 0) }} karyawan belum diinput
                    </p>
                @endif
            </div>
        </div>

        {{-- Import Excel --}}
        <div class="card dark:card-dark">
            <div class="card-body flex flex-col items-center justify-center text-center gap-3 py-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900">
                    <i class="fas fa-file-excel text-lg text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-tight">Import gaji banyak<br>karyawan via Excel</p>
                <div class="flex gap-2">
                    <a href="{{ route('staff.keuangan.salaries.import.form') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-upload"></i> Import
                    </a>
                    <a href="{{ route('staff.keuangan.salaries.template') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-download"></i> Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card dark:card-dark">
        <div class="overflow-x-auto">
            <table class="table dark:table-dark">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Karyawan</th>
                        <th>Bagian</th>
                        <th class="w-28">Status</th>
                        <th class="w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="dark:text-gray-300">
                    @forelse($users as $index => $user)
                    <tr>
                        <td class="text-gray-400">{{ $index + 1 }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm {{ $user->salary_exists ? 'bg-emerald-600' : 'bg-blue-600' }} text-white text-xs font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $user->nip ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm">{{ $user->bagian ?? '-' }}</td>
                        <td>
                            @if($user->salary_exists)
                                <span class="badge badge-success">
                                    <i class="fas fa-check text-[10px]"></i> Sudah
                                </span>
                            @else
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock text-[10px]"></i> Belum
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($user->salary_exists)
                                <a href="{{ route('staff.keuangan.salaries.show', $user->salary_id) }}" 
                                   class="btn btn-sm btn-primary" title="Lihat Slip Gaji">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                            @else
                                <a href="{{ route('staff.keuangan.salaries.input.single', ['user' => $user->id, 'month' => $month, 'year' => $year]) }}" 
                                   class="btn btn-sm btn-success" title="Input Gaji">
                                    <i class="fas fa-plus"></i> Input
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-12 text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fas fa-users-slash text-3xl opacity-40"></i>
                                <p class="text-sm">Tidak ada data karyawan ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
