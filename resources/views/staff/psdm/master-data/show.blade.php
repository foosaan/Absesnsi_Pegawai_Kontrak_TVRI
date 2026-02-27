<x-app-layout title="{{ $type->name }}">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff.psdm.master-data') }}" class="btn btn-secondary btn-icon">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title dark:page-title-dark">{{ $type->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $type->description ?? 'Master Data PSDM' }}</p>
            </div>
        </div>
    </x-slot>

    {{-- Add Value Form --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="POST" action="{{ route('staff.psdm.master-data.values.store', $type) }}" class="flex flex-wrap items-end gap-4">
                @csrf
                <div class="form-group mb-0 flex-1 min-w-48">
                    <label class="form-label">Nilai Baru</label>
                    <input type="text" name="value" class="form-control" 
                           placeholder="Masukkan nilai..." required>
                </div>
                <div class="form-group mb-0 flex-1 min-w-48">
                    <label class="form-label">Deskripsi (opsional)</label>
                    <input type="text" name="description" class="form-control" 
                           placeholder="Keterangan singkat...">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </form>
        </div>
    </div>

    {{-- Values Table --}}
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Nilai</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th class="text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="dark:text-gray-300">
                    @forelse($type->values as $index => $value)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="font-medium text-gray-900 dark:text-white">{{ $value->value }}</td>
                        <td>{{ $value->description ?? '-' }}</td>
                        <td>
                            @if($value->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <form action="{{ route('staff.psdm.master-data.values.destroy', $value) }}" method="POST"
                                  data-confirm="Hapus nilai {{ $value->value }}?" data-confirm-title="Konfirmasi Hapus">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                            <p>Belum ada data. Tambahkan menggunakan form di atas.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
