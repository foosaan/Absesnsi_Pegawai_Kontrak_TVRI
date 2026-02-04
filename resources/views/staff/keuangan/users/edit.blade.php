<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Edit Data Keuangan: {{ $user->name }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('staff.keuangan.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Info Karyawan (Read-only) --}}
                    <div class="mb-6 p-4 bg-gray-50 rounded">
                        <h3 class="text-sm font-bold text-gray-500 mb-2">INFO KARYAWAN</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 block">Nama:</span>
                                <span class="font-medium">{{ $user->name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Email:</span>
                                <span class="font-medium">{{ $user->email }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Tipe:</span>
                                <span class="font-medium">{{ strtoupper($user->employee_type ?? 'N/A') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Identitas Pegawai --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">👤 Identitas Pegawai</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">NIP (Nomor Induk Pegawai)</label>
                                <input type="text" name="nip" value="{{ old('nip', $user->nip) }}"
                                       class="border rounded w-full py-2 px-3 text-gray-700 @error('nip') border-red-500 @enderror"
                                       placeholder="Contoh: 340212010012000" maxlength="20">
                                @error('nip')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">NPWP</label>
                                <input type="text" name="npwp" value="{{ old('npwp', $user->npwp) }}"
                                       class="border rounded w-full py-2 px-3 text-gray-700 @error('npwp') border-red-500 @enderror"
                                       placeholder="00.000.000.0-000.000" maxlength="30">
                                @error('npwp')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Status Pegawai</label>
                                <select name="status_pegawai" class="border rounded w-full py-2 px-3 text-gray-700">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="tetap" {{ old('status_pegawai', $user->status_pegawai) === 'tetap' ? 'selected' : '' }}>Tetap</option>
                                    <option value="kontrak" {{ old('status_pegawai', $user->status_pegawai) === 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                                </select>
                                @error('status_pegawai')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nomor SK</label>
                                <input type="text" name="nomor_sk" value="{{ old('nomor_sk', $user->nomor_sk) }}"
                                       class="border rounded w-full py-2 px-3 text-gray-700 @error('nomor_sk') border-red-500 @enderror"
                                       placeholder="Nomor SK Pengangkatan" maxlength="50">
                                @error('nomor_sk')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal SK</label>
                                <input type="date" name="tanggal_sk" value="{{ old('tanggal_sk', $user->tanggal_sk?->format('Y-m-d')) }}"
                                       class="border rounded w-full py-2 px-3 text-gray-700 @error('tanggal_sk') border-red-500 @enderror">
                                @error('tanggal_sk')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Data Pajak & Bank --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">💳 Data Pajak & Bank</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Status Pajak</label>
                                <select name="status_pajak" class="border rounded w-full py-2 px-3 text-gray-700">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="TK" {{ old('status_pajak', $user->status_pajak) === 'TK' ? 'selected' : '' }}>TK (Tidak Kawin)</option>
                                    <option value="K0" {{ old('status_pajak', $user->status_pajak) === 'K0' ? 'selected' : '' }}>K0 (Kawin tanpa tanggungan)</option>
                                    <option value="K1" {{ old('status_pajak', $user->status_pajak) === 'K1' ? 'selected' : '' }}>K1 (Kawin 1 tanggungan)</option>
                                    <option value="K2" {{ old('status_pajak', $user->status_pajak) === 'K2' ? 'selected' : '' }}>K2 (Kawin 2 tanggungan)</option>
                                    <option value="K3" {{ old('status_pajak', $user->status_pajak) === 'K3' ? 'selected' : '' }}>K3 (Kawin 3 tanggungan)</option>
                                </select>
                                @error('status_pajak')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Gaji Pokok (Rp)</label>
                                <input type="number" name="gaji_pokok" value="{{ old('gaji_pokok', $user->gaji_pokok) }}"
                                       class="border rounded w-full py-2 px-3 text-gray-700 @error('gaji_pokok') border-red-500 @enderror"
                                       placeholder="Contoh: 3000000" min="0" step="0.01">
                                @error('gaji_pokok')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Bank</label>
                                <select name="nama_bank" class="border rounded w-full py-2 px-3 text-gray-700">
                                    <option value="">-- Pilih Bank --</option>
                                    <option value="BRI" {{ old('nama_bank', $user->nama_bank) === 'BRI' ? 'selected' : '' }}>BRI</option>
                                    <option value="BNI" {{ old('nama_bank', $user->nama_bank) === 'BNI' ? 'selected' : '' }}>BNI</option>
                                    <option value="Mandiri" {{ old('nama_bank', $user->nama_bank) === 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
                                    <option value="BCA" {{ old('nama_bank', $user->nama_bank) === 'BCA' ? 'selected' : '' }}>BCA</option>
                                    <option value="BTN" {{ old('nama_bank', $user->nama_bank) === 'BTN' ? 'selected' : '' }}>BTN</option>
                                    <option value="BSI" {{ old('nama_bank', $user->nama_bank) === 'BSI' ? 'selected' : '' }}>BSI</option>
                                    <option value="Lainnya" {{ old('nama_bank', $user->nama_bank) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('nama_bank')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nomor Rekening</label>
                                <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening', $user->nomor_rekening) }}"
                                       class="border rounded w-full py-2 px-3 text-gray-700 @error('nomor_rekening') border-red-500 @enderror"
                                       placeholder="Nomor Rekening Bank" maxlength="30">
                                @error('nomor_rekening')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Data Pribadi --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">📋 Data Pribadi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="border rounded w-full py-2 px-3 text-gray-700">
                                    <option value="">-- Pilih --</option>
                                    <option value="L" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir?->format('Y-m-d')) }}"
                                       class="border rounded w-full py-2 px-3 text-gray-700 @error('tanggal_lahir') border-red-500 @enderror">
                                @error('tanggal_lahir')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">No. Telepon</label>
                                <input type="text" name="no_telepon" value="{{ old('no_telepon', $user->no_telepon) }}"
                                       class="border rounded w-full py-2 px-3 text-gray-700 @error('no_telepon') border-red-500 @enderror"
                                       placeholder="08xxxxxxxxxx" maxlength="20">
                                @error('no_telepon')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" rows="3"
                                      class="border rounded w-full py-2 px-3 text-gray-700 @error('alamat') border-red-500 @enderror"
                                      placeholder="Alamat lengkap tempat tinggal">{{ old('alamat', $user->alamat) }}</textarea>
                            @error('alamat')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t">
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded">
                            💾 Simpan Data Keuangan
                        </button>
                        <a href="{{ route('staff.keuangan.users') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
