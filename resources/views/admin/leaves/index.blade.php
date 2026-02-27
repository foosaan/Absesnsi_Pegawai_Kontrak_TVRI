<x-app-layout title="Status Cuti Karyawan">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900">
                    <i class="fas fa-calendar-minus text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Status Cuti Karyawan</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Pantau pengajuan cuti karyawan (read-only)
                        @if($pendingCount > 0)
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                {{ $pendingCount }} menunggu
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Filter --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.leaves') }}" class="flex flex-wrap items-end gap-4">
                <div class="form-group mb-0">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="mb-6 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
        <div class="flex items-center gap-3">
            <i class="fas fa-info-circle text-blue-500 dark:text-blue-400"></i>
            <p class="text-sm text-blue-700 dark:text-blue-300">
                Halaman ini hanya untuk monitoring. Untuk approve/reject cuti, silakan hubungi <strong>Staff PSDM</strong>.
            </p>
        </div>
    </div>

    {{-- Leave Table --}}
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Tipe</th>
                        <th>Tanggal</th>
                        <th>Durasi</th>
                        <th>Alasan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="dark:text-gray-300">
                    @forelse($leaves as $leave)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm bg-indigo-600 text-white">
                                    {{ strtoupper(substr($leave->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $leave->user->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $leave->user->nip ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-slate-600 text-gray-600 dark:text-gray-300 font-medium">
                                {{ $leave->type_label }}
                            </span>
                        </td>
                        <td>
                            <div class="text-sm">
                                <p>{{ $leave->start_date->format('d M Y') }}</p>
                                <p class="text-xs text-gray-500">s/d {{ $leave->end_date->format('d M Y') }}</p>
                            </div>
                        </td>
                        <td>
                            <span class="font-medium">{{ $leave->total_days }} hari</span>
                        </td>
                        <td>
                            <p class="text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $leave->reason }}">
                                {{ $leave->reason }}
                            </p>
                        </td>
                        <td>
                            @if($leave->status === 'pending')
                                <span class="badge badge-warning">Menunggu</span>
                            @elseif($leave->status === 'approved')
                                <span class="badge badge-success">Disetujui</span>
                                @if($leave->approver)
                                    <p class="text-[10px] text-gray-500 mt-0.5">oleh {{ $leave->approver->name }}</p>
                                @endif
                            @elseif($leave->status === 'rejected')
                                <span class="badge badge-danger">Ditolak</span>
                                @if($leave->rejection_reason)
                                    <p class="text-[10px] text-gray-500 mt-0.5" title="{{ $leave->rejection_reason }}">{{ Str::limit($leave->rejection_reason, 30) }}</p>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-calendar-check text-4xl mb-3 opacity-50"></i>
                            <p>Tidak ada pengajuan cuti</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leaves->hasPages())
        <div class="card-body border-t dark:border-slate-700">
            {{ $leaves->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
