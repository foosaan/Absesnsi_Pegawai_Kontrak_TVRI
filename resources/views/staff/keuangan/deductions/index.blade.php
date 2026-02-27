<x-app-layout title="Jenis Potongan">
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                    <i class="fas fa-minus-circle text-red-600 dark:text-red-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Jenis Potongan</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Kelola jenis potongan gaji intern
                    </p>
                </div>
            </div>
            <button onclick="document.getElementById('addDeductionModal').classList.remove('hidden')" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                <span>Tambah Potongan</span>
            </button>
        </div>
    </x-slot>

    <div class="card dark:card-dark">
        <div class="overflow-x-auto">
            <table class="table dark:table-dark">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Nama Potongan</th>
                        <th>Deskripsi</th>
                        <th class="text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="dark:text-gray-300">
                    @forelse($deductionTypes ?? [] as $type)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="font-medium text-gray-900 dark:text-white">{{ $type->name }}</td>
                        <td>{{ $type->description ?? '-' }}</td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editDeduction({{ $type->id }}, '{{ $type->name }}', '{{ $type->description }}')" 
                                        class="text-amber-500 hover:text-amber-600 dark:hover:text-amber-400 btn-icon">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('staff.keuangan.deductions.destroy', $type) }}" method="POST"
                                      data-confirm="Yakin ingin menghapus jenis potongan ini?" data-confirm-title="Konfirmasi Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 btn-icon">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                            <p>Belum ada jenis potongan.</p>
                            <button onclick="document.getElementById('addDeductionModal').classList.remove('hidden')" class="text-blue-600 hover:underline mt-2">
                                Tambah Baru
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Modal --}}
    <div id="addDeductionModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-md transform transition-all">
            <div class="px-6 py-4 border-b dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Jenis Potongan</h3>
                <button onclick="document.getElementById('addDeductionModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('staff.keuangan.deductions.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="form-group">
                        <label class="form-label">Nama Potongan</label>
                        <input type="text" name="name" class="form-control" required placeholder="Contoh: Koperasi, Denda, dll">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/50 rounded-b-lg flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('addDeductionModal').classList.add('hidden')" class="btn btn-secondary">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editDeductionModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-md transform transition-all">
            <div class="px-6 py-4 border-b dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Jenis Potongan</h3>
                <button onclick="document.getElementById('editDeductionModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div class="form-group">
                        <label class="form-label">Nama Potongan</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/50 rounded-b-lg flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editDeductionModal').classList.add('hidden')" class="btn btn-secondary">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function editDeduction(id, name, description) {
            document.getElementById('editForm').action = `/staff/keuangan/deductions/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('editDeductionModal').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
