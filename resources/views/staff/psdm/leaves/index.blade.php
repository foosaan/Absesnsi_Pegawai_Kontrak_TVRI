<x-app-layout title="Manajemen Cuti">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900">
                    <i class="fas fa-calendar-minus text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Manajemen Cuti</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Kelola pengajuan cuti karyawan
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
            <form method="GET" action="{{ route('staff.psdm.leaves') }}" class="flex flex-wrap items-end gap-4">
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
                        <th>Berkas</th>
                        <th>Status</th>
                        <th>Aksi</th>
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
                            @if($leave->attachment)
                                @php
                                    $ext = pathinfo($leave->attachment, PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png']);
                                @endphp
                                @if($isImage)
                                    <div x-data="{ showPreview: false }">
                                        <button @click="showPreview = true" class="group relative">
                                            <img src="{{ asset('storage/' . $leave->attachment) }}" 
                                                 class="h-10 w-10 rounded-lg object-cover border dark:border-slate-600 group-hover:ring-2 ring-blue-400 transition" 
                                                 alt="Bukti">
                                            <span class="absolute inset-0 bg-black/30 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                                <i class="fas fa-search-plus text-white text-xs"></i>
                                            </span>
                                        </button>
                                        {{-- Preview Modal --}}
                                        <div x-show="showPreview" x-cloak @click="showPreview = false"
                                             class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
                                            <div @click.stop class="relative max-w-2xl max-h-[80vh]">
                                                <img src="{{ asset('storage/' . $leave->attachment) }}" class="max-h-[75vh] rounded-xl shadow-2xl">
                                                <button @click="showPreview = false" 
                                                        class="absolute -top-3 -right-3 h-8 w-8 flex items-center justify-center rounded-full bg-white text-gray-700 shadow-lg hover:bg-gray-100">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ asset('storage/' . $leave->attachment) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                        <i class="fas fa-file-alt"></i>
                                        {{ strtoupper($ext) }}
                                    </a>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
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
                        <td>
                            @if($leave->status === 'pending')
                                <div class="flex items-center gap-2">
                                    {{-- Approve --}}
                                    <form method="POST" action="{{ route('staff.psdm.leaves.approve', $leave) }}" onsubmit="return confirm('Setujui cuti {{ $leave->user->name }}?')">
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
                                            onclick="document.getElementById('reject-modal-{{ $leave->id }}').classList.remove('hidden')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                {{-- Reject Modal --}}
                                <div id="reject-modal-{{ $leave->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tolak Cuti</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                            Tolak pengajuan cuti <strong>{{ $leave->user->name }}</strong> 
                                            ({{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M Y') }})?
                                        </p>
                                        <form method="POST" action="{{ route('staff.psdm.leaves.reject', $leave) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="form-group">
                                                <label class="form-label">Alasan Penolakan <span class="text-red-500">*</span></label>
                                                <textarea name="rejection_reason" rows="3" class="form-control" required placeholder="Masukkan alasan penolakan..."></textarea>
                                            </div>
                                            <div class="flex justify-end gap-3 mt-4">
                                                <button type="button" class="btn btn-secondary" 
                                                        onclick="document.getElementById('reject-modal-{{ $leave->id }}').classList.add('hidden')">
                                                    Batal
                                                </button>
                                                <button type="submit" class="btn btn-danger">Tolak Cuti</button>
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
                        <td colspan="8" class="text-center py-8 text-gray-500 dark:text-gray-400">
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
