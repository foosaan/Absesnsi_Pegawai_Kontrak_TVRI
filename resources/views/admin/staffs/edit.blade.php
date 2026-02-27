<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">✏️ Edit Staff: {{ $user->name }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('admin.staffs.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('name') border-red-500 @enderror">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">NIP</label>
                        <input type="text" name="nip" value="{{ old('nip', $user->nip) }}"
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('nip') border-red-500 @enderror"
                               placeholder="Nomor Induk Pegawai">
                        @error('nip')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('email') border-red-500 @enderror">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Role</label>
                        <select name="role" required class="border rounded w-full py-2 px-3 text-gray-700 @error('role') border-red-500 @enderror">
                            <option value="staff_psdm" {{ old('role', $user->role) == 'staff_psdm' ? 'selected' : '' }}>Staff PSDM</option>
                            <option value="staff_keuangan" {{ old('role', $user->role) == 'staff_keuangan' ? 'selected' : '' }}>Staff Keuangan</option>
                        </select>
                        @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Password Baru (opsional)</label>
                        <input type="password" name="password"
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('password') border-red-500 @enderror"
                               placeholder="Kosongkan jika tidak ingin mengubah">
                        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation"
                               class="border rounded w-full py-2 px-3 text-gray-700"
                               placeholder="Ulangi password baru">
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded">
                            Update
                        </button>
                        <a href="{{ route('admin.staffs') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
