<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Hitung Gaji</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('staff.keuangan.calculate.process') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Pilih Karyawan</label>
                        <select name="user_id" required class="border rounded w-full py-2 px-3 text-gray-700 @error('user_id') border-red-500 @enderror">
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ strtoupper($user->employee_type ?? 'N/A') }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Bulan</label>
                            <select name="month" required class="border rounded w-full py-2 px-3 text-gray-700">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ old('month', $currentMonth) == $m ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tahun</label>
                            <select name="year" required class="border rounded w-full py-2 px-3 text-gray-700">
                                @for($y = 2024; $y <= 2030; $y++)
                                    <option value="{{ $y }}" {{ old('year', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Gaji Pokok (Rp)</label>
                        <input type="number" name="base_salary" value="{{ old('base_salary', 3000000) }}" required
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('base_salary') border-red-500 @enderror"
                               placeholder="Contoh: 3000000">
                        @error('base_salary')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6">
                        <h4 class="font-bold text-blue-700 mb-2">ℹ️ Info Perhitungan:</h4>
                        <ul class="text-sm text-blue-600 space-y-1">
                            <li>• Potongan Terlambat: <strong>2%</strong> per hari</li>
                            <li>• Potongan Absen: <strong>4%</strong> per hari</li>
                            <li>• Gaji Akhir = Gaji Pokok - Total Potongan</li>
                        </ul>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded">
                            🧮 Hitung Gaji
                        </button>
                        <a href="{{ route('staff.keuangan.salaries') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
