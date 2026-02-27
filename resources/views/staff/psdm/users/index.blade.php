<x-app-layout title="Manajemen Pegawai">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                    <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Data Pegawai</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Kelola data kepegawaian
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('staff.psdm.users.import') }}" class="btn btn-success">
                    <i class="fas fa-file-excel"></i>
                    <span class="hidden sm:inline">Import Excel</span>
                </a>
                <a href="{{ route('staff.psdm.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="hidden sm:inline">Tambah Pegawai</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Filter --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('staff.psdm.users') }}" class="flex flex-wrap items-end gap-4">
                <div class="form-group mb-0 flex-1 min-w-48">
                    <label class="form-label">Cari Pegawai</label>
                    <div class="relative">
                        <i class="fas fa-search form-control-icon"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control form-control-with-icon" 
                               placeholder="Nama atau Email...">
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div x-data="{
        selectedIds: [],
        allIds: {{ json_encode($users->pluck('id')->toArray()) }},
        get allSelected() { return this.allIds.length > 0 && this.selectedIds.length === this.allIds.length },
        get someSelected() { return this.selectedIds.length > 0 },
        toggleAll() {
            if (this.allSelected) {
                this.selectedIds = [];
            } else {
                this.selectedIds = [...this.allIds];
            }
        },
        toggleId(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx > -1) { this.selectedIds.splice(idx, 1); }
            else { this.selectedIds.push(id); }
        }
    }">
        {{-- Floating Bulk Action Bar --}}
        <div x-show="someSelected" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             x-cloak
             class="mb-4">
            <div class="card border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20">
                <div class="card-body flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/50">
                            <i class="fas fa-check-double text-sm text-red-600 dark:text-red-400"></i>
                        </div>
                        <span class="text-sm font-medium text-red-800 dark:text-red-300">
                            <span x-text="selectedIds.length"></span> pegawai dipilih
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="selectedIds = []" class="btn btn-sm btn-secondary">
                            Batal
                        </button>
                        <form method="POST" action="{{ route('staff.psdm.users.bulk-delete') }}"
                              data-confirm="Yakin ingin menghapus semua pegawai yang dipilih? Semua data terkait (absensi, gaji, cuti, dll) juga akan dihapus!"
                              data-confirm-title="Konfirmasi Hapus Massal">
                            @csrf
                            <template x-for="id in selectedIds" :key="id">
                                <input type="hidden" name="user_ids[]" :value="id">
                            </template>
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash mr-1"></i>
                                Hapus (<span x-text="selectedIds.length"></span>)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" 
                                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 cursor-pointer"
                                       @click="toggleAll()"
                                       :checked="allSelected"
                                       :indeterminate="someSelected && !allSelected">
                            </th>
                            <th class="w-12">No</th>
                            <th>Identitas</th>
                            <th>Posisi</th>
                            <th>Status</th>
                            <th>Jenis Kelamin</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="dark:text-gray-300">
                        @forelse($users as $index => $user)
                        <tr :class="selectedIds.includes({{ $user->id }}) ? 'bg-blue-50 dark:bg-blue-900/10' : ''">
                            <td>
                                <input type="checkbox" 
                                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 cursor-pointer"
                                       value="{{ $user->id }}"
                                       @click="toggleId({{ $user->id }})"
                                       :checked="selectedIds.includes({{ $user->id }})">
                            </td>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar avatar-sm bg-blue-600 text-white">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->nip ?? 'Belum ada NIP' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->jabatan ?? '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->bagian ?? '-' }}</p>
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $user->status_pegawai ?? 'Aktif' }}</span>
                            </td>
                            <td>
                                @if($user->jenis_kelamin == 'L')
                                    <span class="badge bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">Laki-laki</span>
                                @elseif($user->jenis_kelamin == 'P')
                                    <span class="badge bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300">Perempuan</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('staff.psdm.users.edit', $user) }}" 
                                       class="btn btn-sm btn-warning btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('staff.psdm.users.delete', $user) }}" method="POST"
                                          data-confirm="Yakin ingin menghapus pegawai ini? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Konfirmasi Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <i class="fas fa-users-slash text-4xl mb-3 opacity-50"></i>
                                <p>Tidak ada data pegawai ditemukan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
            <div class="card-body border-t dark:border-slate-700">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
