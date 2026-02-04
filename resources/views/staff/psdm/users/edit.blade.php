<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Edit User: {{ $user->name }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('staff.psdm.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('name') border-red-500 @enderror">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('email') border-red-500 @enderror">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Tipe Karyawan</label>
                        <select name="employee_type" class="border rounded w-full py-2 px-3 text-gray-700">
                            <option value="" {{ old('employee_type', $user->employee_type) == '' ? 'selected' : '' }}>Karyawan Biasa</option>
                            <option value="ob" {{ old('employee_type', $user->employee_type) === 'ob' ? 'selected' : '' }}>Office Boy (OB)</option>
                            <option value="satpam" {{ old('employee_type', $user->employee_type) === 'satpam' ? 'selected' : '' }}>Satpam</option>
                        </select>
                        @error('employee_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Tipe Absensi *</label>
                        <select name="attendance_type" required class="border rounded w-full py-2 px-3 text-gray-700">
                            <option value="normal" {{ old('attendance_type', $user->attendance_type) === 'normal' ? 'selected' : '' }}>🕐 Normal (08:00 - 16:00)</option>
                            <option value="shift" {{ old('attendance_type', $user->attendance_type) === 'shift' ? 'selected' : '' }}>🔄 Shift (3 Shift)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Normal: jam kerja standar | Shift: 3 shift (00-08, 08-16, 16-24)</p>
                        @error('attendance_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Password Baru <span class="font-normal text-gray-400">(kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password" autocomplete="new-password"
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('password') border-red-500 @enderror"
                               placeholder="Kosongkan jika tidak ingin mengubah">
                        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                               class="border rounded w-full py-2 px-3 text-gray-700"
                               placeholder="Konfirmasi password baru">
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-6">
                        <p class="text-sm text-blue-600">
                            💡 <strong>Info:</strong> Untuk edit data keuangan (NIP, NPWP, rekening bank, gaji pokok), silakan hubungi Staff Keuangan.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded">
                            💾 Simpan
                        </button>
                        <a href="{{ route('staff.psdm.users') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
