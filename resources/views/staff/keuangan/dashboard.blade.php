<x-app-layout title="Dashboard Keuangan">
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900">
                <i class="fas fa-chart-line text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div>
                <h1 class="page-title dark:page-title-dark">Dashboard Keuangan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Selamat datang, {{ auth()->user()->name }}</p>
            </div>
        </div>
    </x-slot>

    {{-- Stat Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="stat-card">
            <div>
                <p class="stat-card-label">Total Karyawan</p>
                <p class="stat-card-value">{{ $totalUsers ?? 0 }}</p>
            </div>
            <div class="stat-card-icon bg-blue-100 dark:bg-blue-900">
                <i class="fas fa-users text-xl text-blue-600 dark:text-blue-400"></i>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-card-label">Total Gaji Bulan Ini</p>
                <p class="stat-card-value text-lg">Rp {{ number_format($totalSalaryThisMonth ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="stat-card-icon bg-emerald-100 dark:bg-emerald-900">
                <i class="fas fa-money-bill-wave text-xl text-emerald-600 dark:text-emerald-400"></i>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-card-label">Sudah Dibayar</p>
                <p class="stat-card-value">{{ $paidCount ?? 0 }}</p>
                @if(($paidCount ?? 0) > 0)
                <span class="stat-trend-up">
                    <i class="fas fa-check"></i> Selesai
                </span>
                @endif
            </div>
            <div class="stat-card-icon bg-green-100 dark:bg-green-900">
                <i class="fas fa-check-circle text-xl text-green-600 dark:text-green-400"></i>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-card-label">Belum Diinput</p>
                <p class="stat-card-value">{{ $pendingCount ?? 0 }}</p>
                @if(($pendingCount ?? 0) > 0)
                <span class="stat-trend-down">
                    <i class="fas fa-clock"></i> Pending
                </span>
                @endif
            </div>
            <div class="stat-card-icon bg-amber-100 dark:bg-amber-900">
                <i class="fas fa-hourglass-half text-xl text-amber-600 dark:text-amber-400"></i>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="card dark:card-dark mb-6">
        <div class="card-header dark:card-header-dark">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-bolt text-amber-500 mr-2"></i>
                Aksi Cepat
            </h3>
        </div>
        <div class="card-body">
            <div class="grid gap-3 sm:grid-cols-3">
                <a href="{{ route('staff.keuangan.users') }}" class="btn btn-success w-full justify-start">
                    <i class="fas fa-edit"></i>
                    <span>Input Gaji</span>
                </a>
                <a href="{{ route('staff.keuangan.salaries') }}" class="btn btn-primary w-full justify-start">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Data Gaji</span>
                </a>
                <a href="{{ route('staff.keuangan.deductions.index') }}" class="btn btn-secondary w-full justify-start">
                    <i class="fas fa-minus-circle"></i>
                    <span>Jenis Potongan</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Recent Salary Data --}}
    <div class="card dark:card-dark">
        <div class="card-header dark:card-header-dark flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-clock text-blue-500 mr-2"></i>
                Data Gaji Terbaru
            </h3>
            <a href="{{ route('staff.keuangan.salaries') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="table dark:table-dark">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Periode</th>
                        <th>Gaji Diterima</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="dark:text-gray-300">
                    @forelse($recentSalaries ?? [] as $salary)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm bg-blue-600 text-white">
                                    {{ strtoupper(substr($salary->user->name ?? 'N', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $salary->user->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $salary->user->nip ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ \Carbon\Carbon::create()->month((int) $salary->month)->translatedFormat('M') }} {{ $salary->year }}
                        </td>
                        <td class="font-medium text-gray-900 dark:text-white">
                            Rp {{ number_format($salary->final_salary, 0, ',', '.') }}
                        </td>
                        <td>
                            @if($salary->status === 'paid')
                                <span class="badge badge-success">Dibayar</span>
                            @elseif($salary->status === 'approved')
                                <span class="badge badge-info">Disetujui</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('staff.keuangan.salaries.show', $salary) }}" class="btn btn-sm btn-primary btn-icon">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                            <p>Belum ada data gaji</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
