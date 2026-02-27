<x-app-layout title="Input Gaji">
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                    <i class="fas fa-edit text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Input Gaji</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') }} {{ $year }}
                    </p>
                </div>
            </div>
            <a href="{{ route('staff.keuangan.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    {{-- Period Selection --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('staff.keuangan.salaries.input') }}" class="flex flex-wrap items-end gap-4">
                <div class="form-group mb-0 min-w-32">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month((int) $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mb-0 min-w-24">
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

    {{-- Progress Card --}}
    <div class="card mb-6">
        <div class="card-body">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress Input</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ $completedCount ?? 0 }} / {{ $totalUsers ?? 0 }} karyawan
                </span>
            </div>
            <div class="progress">
                @php 
                    $totalUsersValue = $totalUsers ?? 0;
                    $completedValue = $completedCount ?? 0;
                    $percent = $totalUsersValue > 0 ? ($completedValue / $totalUsersValue) * 100 : 0; 
                @endphp
                <div class="progress-bar bg-emerald-500" style="width: {{ $percent }}%"></div>
            </div>
        </div>
    </div>

    {{-- Users List --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-users text-blue-500 mr-2"></i>
                Daftar Karyawan
            </h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Karyawan</th>
                        <th>Bagian</th>
                        <th>Gaji Pokok</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="dark:text-gray-300">
                    @forelse($users as $index => $user)
                    <tr>
                        <td>{{ (method_exists($users, 'firstItem') ? $users->firstItem() : 1) + $index }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm {{ $user->salary_exists ? 'bg-emerald-600' : 'bg-blue-600' }} text-white">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->nip ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->bagian ?? '-' }}</td>
                        <td>Rp {{ number_format($user->gaji_pokok ?? 0, 0, ',', '.') }}</td>
                        <td>
                            @if($user->salary_exists)
                                <span class="badge badge-success">
                                    <i class="fas fa-check mr-1"></i> Sudah Input
                                </span>
                            @else
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock mr-1"></i> Belum Input
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($user->salary_exists)
                                <a href="{{ route('staff.keuangan.salaries.show', $user->salary_id) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                    <span>Lihat</span>
                                </a>
                            @else
                                <a href="{{ route('staff.keuangan.salaries.input.single', ['user' => $user->id, 'month' => $month, 'year' => $year]) }}" 
                                   class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i>
                                    <span>Input</span>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-users-slash text-4xl mb-3 opacity-50"></i>
                            <p>Tidak ada data karyawan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($users, 'hasPages') && $users->hasPages())
        <div class="card-body border-t dark:border-slate-700">
            {{ $users->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
