<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Tambah Pengumuman</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('staff.psdm.announcements.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Judul</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('title') border-red-500 @enderror">
                        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Isi Pengumuman</label>
                        <textarea name="content" rows="5" required
                                  class="border rounded w-full py-2 px-3 text-gray-700 @error('content') border-red-500 @enderror">{{ old('content') }}</textarea>
                        @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 mr-2">
                            <span class="text-sm text-gray-700">Aktifkan pengumuman</span>
                        </label>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded">
                            Simpan
                        </button>
                        <a href="{{ route('staff.psdm.announcements') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
