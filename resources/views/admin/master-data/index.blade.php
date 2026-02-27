<x-app-layout title="Master Data">
    <x-slot name="header">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Master Data</h1>
    </x-slot>

    {{-- Flash Messages --}}

    {{-- Tabs --}}
    <div class="mb-6">
        <div class="flex gap-2">
            <a href="{{ route('admin.master-data', ['scope' => 'psdm']) }}" 
               class="btn {{ $currentScope === 'psdm' ? 'btn-primary' : 'btn-secondary' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Master Data PSDM</span>
            </a>
            <a href="{{ route('admin.master-data', ['scope' => 'keuangan']) }}" 
               class="btn {{ $currentScope === 'keuangan' ? 'btn-success' : 'btn-secondary' }}">
                <i class="fas fa-money-bill-wave"></i>
                <span>Master Data Keuangan</span>
            </a>
        </div>
    </div>

    {{-- Form Tambah Kategori --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">Tambah Kategori {{ $currentScope === 'psdm' ? 'PSDM' : 'Keuangan' }}</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.master-data.store') }}" class="flex flex-wrap gap-4 items-end">
                @csrf
                <input type="hidden" name="scope" value="{{ $currentScope }}">
                <div class="form-field mb-0 flex-1 min-w-[200px]">
                    <label class="form-label">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required 
                           placeholder="{{ $currentScope === 'psdm' ? 'Contoh: Jabatan, Bagian, Status...' : 'Contoh: Jenis Tunjangan, Kategori Potongan...' }}"
                           class="form-control @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-field mb-0 flex-1 min-w-[200px]">
                    <label class="form-label">Deskripsi</label>
                    <input type="text" name="description" placeholder="Deskripsi singkat (opsional)"
                           class="form-control">
                </div>
                <button type="submit" class="btn {{ $currentScope === 'psdm' ? 'btn-primary' : 'btn-success' }}">
                    <i class="fas fa-save"></i>
                    <span>Simpan</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Daftar Kategori --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Kategori {{ $currentScope === 'psdm' ? 'PSDM' : 'Keuangan' }}</h3>
            <span class="text-sm text-gray-500 dark:text-slate-400">{{ $types->count() }} kategori</span>
        </div>
        <div class="card-body p-0">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Jumlah Nilai</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                        <tr x-data="{ editing: false }">
                            <td>
                                <template x-if="!editing">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $type->name }}</span>
                                </template>
                                <template x-if="editing">
                                    <form method="POST" action="{{ route('admin.master-data.update', $type) }}" class="flex gap-2 items-center">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" value="{{ $type->name }}" 
                                               class="form-control py-1" required>
                                        <input type="text" name="description" value="{{ $type->description }}" 
                                               placeholder="Deskripsi" class="form-control py-1">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" @click="editing = false" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </template>
                            </td>
                            <td class="text-gray-600 dark:text-slate-400" x-show="!editing">{{ $type->description ?? '-' }}</td>
                            <td x-show="editing"></td>
                            <td class="text-center">
                                <span class="badge {{ $currentScope === 'psdm' ? 'badge-primary' : 'badge-success' }}">
                                    {{ $type->values_count }} nilai
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center gap-2" x-show="!editing">
                                    <button @click="editing = true" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit</span>
                                    </button>
                                    <a href="{{ route('admin.master-data.show', $type) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-cog"></i>
                                        <span>Kelola</span>
                                    </a>
                                    <form action="{{ route('admin.master-data.destroy', $type) }}" method="POST" class="inline"
                                          data-confirm="Yakin ingin menghapus kategori ini? Semua nilai di dalamnya akan ikut terhapus." data-confirm-title="Konfirmasi Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-500 dark:text-slate-400 py-8">
                                <i class="fas fa-database text-4xl mb-2 opacity-50"></i>
                                <p>Belum ada kategori master data {{ $currentScope === 'psdm' ? 'PSDM' : 'Keuangan' }}.</p>
                                <p class="text-sm">Silakan tambah di form di atas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
