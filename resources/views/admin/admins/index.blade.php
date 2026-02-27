<x-app-layout title="Kelola Admin">
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Kelola Admin</h1>
            <a href="{{ route('admin.admins.create') }}" class="btn btn-danger">
                <i class="fas fa-plus"></i>
                <span>Tambah Admin</span>
            </a>
        </div>
    </x-slot>

    {{-- Flash Messages --}}

    {{-- Filter Card --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div class="form-field mb-0 flex-1">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Nama atau email...">
                </div>
                @if(request('search'))
                <div class="flex gap-2">
                    <a href="{{ route('admin.admins') }}" class="btn btn-secondary">Reset</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Admin Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Admin</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th class="text-center">Dibuat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                        <tr class="{{ $admin->id === auth()->id() ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                            <td>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $admin->name }}</div>
                                @if($admin->id === auth()->id())
                                    <span class="text-xs text-blue-500">(Anda)</span>
                                @endif
                            </td>
                            <td class="text-gray-600 dark:text-slate-400">{{ $admin->email }}</td>
                            <td class="text-center text-sm text-gray-500 dark:text-slate-400">
                                {{ $admin->created_at->format('d M Y') }}
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit</span>
                                    </a>
                                    @if($admin->id !== auth()->id())
                                    <form action="{{ route('admin.admins.delete', $admin) }}" method="POST" class="inline"
                                          data-confirm="Yakin ingin menghapus admin {{ $admin->name }}?" data-confirm-title="Konfirmasi Hapus">
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
                            <td colspan="4" class="text-center text-gray-500 dark:text-slate-400 py-8">
                                <i class="fas fa-user-shield text-4xl mb-2 opacity-50"></i>
                                <p>Tidak ada data admin</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($admins->hasPages())
        <div class="card-footer">
            {{ $admins->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
