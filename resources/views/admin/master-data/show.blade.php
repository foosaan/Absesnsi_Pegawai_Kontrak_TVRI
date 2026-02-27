<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('admin.master-data') }}" class="text-gray-500 hover:text-gray-700 text-sm">← Master Data</a>
                <h2 class="font-semibold text-xl text-gray-800">{{ $type->name }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            

            <!-- Info Kategori -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-lg">{{ $type->name }}</h3>
                        @if($type->description)
                            <p class="text-gray-500 text-sm mt-1">{{ $type->description }}</p>
                        @endif
                    </div>
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                        {{ $type->values->count() }} nilai
                    </span>
                </div>
            </div>

            <!-- Form Tambah Nilai -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="font-bold mb-4">➕ Tambah Nilai Baru</h3>
                <form method="POST" action="{{ route('admin.master-data.values.store', $type) }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Nilai <span class="text-red-500">*</span></label>
                        <input type="text" name="value" required placeholder="Masukkan nilai..."
                               class="w-full border rounded px-3 py-2 text-sm @error('value') border-red-500 @enderror">
                        @error('value')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Deskripsi</label>
                        <input type="text" name="description" placeholder="Deskripsi (opsional)"
                               class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-medium">
                        Tambah
                    </button>
                </form>
            </div>

            <!-- Daftar Nilai -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h3 class="font-bold">📝 Daftar Nilai</h3>
                </div>
                <div class="divide-y">
                    @forelse($type->values as $value)
                    <div class="p-4 flex justify-between items-center hover:bg-gray-50">
                        <div>
                            <div class="font-medium">{{ $value->value }}</div>
                            @if($value->description)
                                <div class="text-gray-500 text-sm">{{ $value->description }}</div>
                            @endif
                        </div>
                        <form action="{{ route('admin.master-data.values.destroy', $value) }}" method="POST"
                              data-confirm="Yakin ingin menghapus nilai ini?" data-confirm-title="Konfirmasi Hapus">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                Hapus
                            </button>
                        </form>
                    </div>
                    @empty
                    <div class="p-8 text-center text-gray-400">
                        Belum ada nilai untuk kategori ini.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
