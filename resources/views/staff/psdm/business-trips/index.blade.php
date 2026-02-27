<x-app-layout title="Manajemen Dinas Luar">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                    <i class="fas fa-briefcase text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Manajemen Dinas Luar</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Kelola pengajuan dinas luar karyawan
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
            <form method="GET" action="{{ route('staff.psdm.business-trips') }}" class="flex flex-wrap items-end gap-4">
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

    {{-- Business Trip Table --}}
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Tujuan</th>
                        <th>Tanggal</th>
                        <th>Durasi</th>
                        <th>Keperluan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="dark:text-gray-300">
                    @forelse($trips as $trip)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm bg-blue-600 text-white">
                                    {{ strtoupper(substr($trip->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $trip->user->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $trip->user->nip ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300 font-medium">
                                {{ $trip->destination }}
                            </span>
                        </td>
                        <td>
                            <div class="text-sm">
                                <p>{{ $trip->start_date->format('d M Y') }}</p>
                                <p class="text-xs text-gray-500">s/d {{ $trip->end_date->format('d M Y') }}</p>
                            </div>
                        </td>
                        <td>
                            <span class="font-medium">{{ $trip->total_days }} hari</span>
                        </td>
                        <td>
                            <p class="text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $trip->purpose }}">
                                {{ $trip->purpose }}
                            </p>
                        </td>
                        <td>
                            @if($trip->status === 'pending')
                                <span class="badge badge-warning">Menunggu</span>
                            @elseif($trip->status === 'approved')
                                <span class="badge badge-success">Disetujui</span>
                                @if($trip->approver)
                                    <p class="text-[10px] text-gray-500 mt-0.5">oleh {{ $trip->approver->name }}</p>
                                @endif
                            @elseif($trip->status === 'rejected')
                                <span class="badge badge-danger">Ditolak</span>
                                @if($trip->rejection_reason)
                                    <p class="text-[10px] text-gray-500 mt-0.5" title="{{ $trip->rejection_reason }}">{{ Str::limit($trip->rejection_reason, 30) }}</p>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($trip->status === 'pending')
                                <div class="flex items-center gap-2">
                                    {{-- Approve --}}
                                    <form method="POST" action="{{ route('staff.psdm.business-trips.approve', $trip) }}" onsubmit="return confirm('Setujui dinas luar {{ $trip->user->name }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm" title="Setujui">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>

                                    {{-- Reject (with modal trigger) --}}
                                    <button type="button" 
                                            class="btn btn-danger btn-sm"
                                            title="Tolak"
                                            onclick="document.getElementById('reject-modal-{{ $trip->id }}').classList.remove('hidden')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                {{-- Reject Modal --}}
                                <div id="reject-modal-{{ $trip->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tolak Dinas Luar</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                            Tolak pengajuan dinas luar <strong>{{ $trip->user->name }}</strong> 
                                            ke {{ $trip->destination }} ({{ $trip->start_date->format('d M') }} - {{ $trip->end_date->format('d M Y') }})?
                                        </p>
                                        <form method="POST" action="{{ route('staff.psdm.business-trips.reject', $trip) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="form-group">
                                                <label class="form-label">Alasan Penolakan <span class="text-red-500">*</span></label>
                                                <textarea name="rejection_reason" rows="3" class="form-control" required placeholder="Masukkan alasan penolakan..."></textarea>
                                            </div>
                                            <div class="flex justify-end gap-3 mt-4">
                                                <button type="button" class="btn btn-secondary" 
                                                        onclick="document.getElementById('reject-modal-{{ $trip->id }}').classList.add('hidden')">
                                                    Batal
                                                </button>
                                                <button type="submit" class="btn btn-danger">Tolak Dinas Luar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-briefcase text-4xl mb-3 opacity-50"></i>
                            <p>Tidak ada pengajuan dinas luar</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($trips->hasPages())
        <div class="card-body border-t dark:border-slate-700">
            {{ $trips->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
