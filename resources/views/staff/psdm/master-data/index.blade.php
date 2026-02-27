<x-app-layout title="Master Data PSDM">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900">
                    <i class="fas fa-database text-violet-600 dark:text-violet-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Master Data PSDM</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Kelola data referensi kepegawaian</p>
                </div>
            </div>
            {{-- Tombol Tambah Kategori --}}
            <button onclick="document.getElementById('add-modal').classList.remove('hidden')" 
                    class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Kategori
            </button>
        </div>
    </x-slot>

    {{-- Modal Tambah Kategori --}}
    <div id="add-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Kategori Baru</h3>
                <button onclick="document.getElementById('add-modal').classList.add('hidden')" 
                        class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 flex items-center justify-center">
                    <i class="fas fa-times text-gray-400"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('staff.psdm.master-data.store') }}" class="space-y-4">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Jabatan, Bagian..." class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi <span class="text-gray-400 text-xs">(opsional)</span></label>
                    <input type="text" name="description" placeholder="Keterangan singkat..." class="form-control">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-modal').classList.add('hidden')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Kategori Cards --}}
    @if(($types ?? collect())->count() > 0)
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($types as $type)
        <div class="card hover:shadow-lg transition-all group" x-data="{ showActions: false }">
            <a href="{{ route('staff.psdm.master-data.show', $type) }}" class="block card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-xl bg-violet-100 dark:bg-violet-900/50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-folder-open text-violet-600 dark:text-violet-400"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                                {{ $type->name }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $type->description ?? 'Tidak ada deskripsi' }}
                            </p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400 flex-shrink-0">
                        {{ $type->values_count }} nilai
                    </span>
                </div>
            </a>
            {{-- Bottom actions bar --}}
            <div class="px-5 pb-4 pt-0 flex items-center justify-between">
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    <i class="fas fa-arrow-right mr-1"></i> Klik untuk kelola nilai
                </span>
                <div class="flex items-center gap-1">
                    <button onclick="event.stopPropagation(); openEditModal('{{ $type->id }}', '{{ addslashes($type->name) }}', '{{ addslashes($type->description) }}')" 
                            class="h-7 w-7 rounded-md text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 flex items-center justify-center transition-colors"
                            title="Edit">
                        <i class="fas fa-pen text-[11px]"></i>
                    </button>
                    <form action="{{ route('staff.psdm.master-data.destroy', $type) }}" method="POST" class="inline"
                          data-confirm="Yakin hapus kategori '{{ $type->name }}'? Semua data di dalamnya akan ikut terhapus." data-confirm-title="Hapus Kategori">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="h-7 w-7 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center justify-center transition-colors"
                                title="Hapus">
                            <i class="fas fa-trash text-[11px]"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    {{-- Empty State --}}
    <div class="card py-16 text-center">
        <div class="flex flex-col items-center justify-center">
            <div class="bg-violet-50 dark:bg-violet-900/20 rounded-full h-20 w-20 flex items-center justify-center mb-5">
                <i class="fas fa-database text-4xl text-violet-300 dark:text-violet-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Belum ada kategori</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mb-5">
                Buat kategori master data seperti Jabatan, Bagian, atau Status Pegawai untuk mengelola data referensi.
            </p>
            <button onclick="document.getElementById('add-modal').classList.remove('hidden')" 
                    class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Kategori Pertama
            </button>
        </div>
    </div>
    @endif

    {{-- Modal Edit Kategori --}}
    <div id="edit-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Kategori</h3>
                <button onclick="document.getElementById('edit-modal').classList.add('hidden')" 
                        class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 flex items-center justify-center">
                    <i class="fas fa-times text-gray-400"></i>
                </button>
            </div>
            <form id="edit-form" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit-name" required class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi <span class="text-gray-400 text-xs">(opsional)</span></label>
                    <input type="text" name="description" id="edit-description" class="form-control">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-modal').classList.add('hidden')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, description) {
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-description').value = description || '';
            document.getElementById('edit-form').action = '{{ url("staff/psdm/master-data") }}/' + id;
            document.getElementById('edit-modal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
