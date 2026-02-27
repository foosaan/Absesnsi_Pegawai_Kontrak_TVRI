<x-app-layout title="Kelola Staff">
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Kelola Staff</h1>
            <a href="{{ route('admin.staffs.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                <span>Tambah Staff</span>
            </a>
        </div>
    </x-slot>

    {{-- Flash Messages --}}

    {{-- Filter Card --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div class="form-field mb-0">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Role</option>
                        <option value="staff_psdm" {{ request('role') == 'staff_psdm' ? 'selected' : '' }}>Staff PSDM</option>
                        <option value="staff_keuangan" {{ request('role') == 'staff_keuangan' ? 'selected' : '' }}>Staff Keuangan</option>
                    </select>
                </div>
                <div class="form-field mb-0 flex-1">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Nama atau email...">
                </div>
                @if(request()->hasAny(['role', 'search']))
                <div class="flex gap-2">
                    <a href="{{ route('admin.staffs') }}" class="btn btn-secondary">Reset</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Staff Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Staff</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th class="text-center">Role</th>
                            <th class="text-center">Dibuat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffs as $staff)
                        <tr class="{{ $staff->id === auth()->id() ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                            <td>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $staff->name }}</div>
                                @if($staff->id === auth()->id())
                                    <span class="text-xs text-blue-500">(Anda)</span>
                                @endif
                            </td>
                            <td class="text-gray-600 dark:text-slate-400">{{ $staff->email }}</td>
                            <td class="text-center">
                                @if($staff->role === 'admin')
                                    <span class="badge badge-danger">Admin</span>
                                @elseif($staff->role === 'staff_psdm')
                                    <span class="badge badge-primary">Staff PSDM</span>
                                @else
                                    <span class="badge badge-success">Staff Keuangan</span>
                                @endif
                            </td>
                            <td class="text-center text-sm text-gray-500 dark:text-slate-400">
                                {{ $staff->created_at->format('d M Y') }}
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.staffs.edit', $staff) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit</span>
                                    </a>
                                    @if($staff->id !== auth()->id())
                                    <form action="{{ route('admin.staffs.delete', $staff) }}" method="POST" class="inline"
                                          data-confirm="Yakin ingin menghapus staff {{ $staff->name }}?" data-confirm-title="Konfirmasi Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 dark:text-slate-400 py-8">
                                <i class="fas fa-users text-4xl mb-2 opacity-50"></i>
                                <p>Tidak ada data staff</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($staffs->hasPages())
        <div class="card-footer">
            {{ $staffs->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
