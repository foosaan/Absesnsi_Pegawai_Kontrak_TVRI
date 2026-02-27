<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">➕ Tambah Admin</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('admin.admins.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('name') border-red-500 @enderror"
                               placeholder="Nama lengkap">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">NIP</label>
                        <input type="text" name="nip" value="{{ old('nip') }}"
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('nip') border-red-500 @enderror"
                               placeholder="Nomor Induk Pegawai">
                        @error('nip')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('email') border-red-500 @enderror"
                               placeholder="email@domain.com">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('password') border-red-500 @enderror"
                               placeholder="Minimal 8 karakter">
                        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required
                               class="border rounded w-full py-2 px-3 text-gray-700"
                               placeholder="Ulangi password">
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded">
                            Simpan
                        </button>
                        <a href="{{ route('admin.admins') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
