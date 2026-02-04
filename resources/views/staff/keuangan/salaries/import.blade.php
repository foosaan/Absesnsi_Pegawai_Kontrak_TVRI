<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">📥 Import Gaji dari Excel</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Instructions --}}
                <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6">
                    <h4 class="font-bold text-blue-700 mb-2">📋 Petunjuk Import Excel</h4>
                    <ol class="list-decimal list-inside text-sm text-blue-600 space-y-1">
                        <li>Download template Excel terlebih dahulu</li>
                        <li>Isi data gaji sesuai kolom yang tersedia</li>
                        <li>Pastikan NIP atau Nama karyawan sudah terdaftar di sistem</li>
                        <li>Upload file dan pilih periode gaji</li>
                        <li>Data akan otomatis tersimpan ke database</li>
                    </ol>
                </div>

                {{-- Download Template --}}
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-green-700">📄 Download Template Excel</h4>
                            <p class="text-sm text-green-600">Template sudah berisi daftar karyawan yang terdaftar</p>
                        </div>
                        <a href="{{ route('staff.keuangan.salaries.template') }}" 
                           class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                            ⬇️ Download Template
                        </a>
                    </div>
                </div>

                {{-- Format Table --}}
                <div class="mb-6">
                    <h4 class="font-bold text-gray-700 mb-2">📊 Format Kolom Excel</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="border px-2 py-1">NIP</th>
                                    <th class="border px-2 py-1">Nama</th>
                                    <th class="border px-2 py-1">Gaji Pokok</th>
                                    <th class="border px-2 py-1">Potongan KPPN</th>
                                    <th class="border px-2 py-1">Simpanan Wajib</th>
                                    <th class="border px-2 py-1">Kredit Uang</th>
                                    <th class="border px-2 py-1">Kredit Toko</th>
                                    <th class="border px-2 py-1">Dharma Wanita</th>
                                    <th class="border px-2 py-1">BPJS</th>
                                    <th class="border px-2 py-1">Gaji Diterima</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-gray-500">
                                    <td class="border px-2 py-1 text-center">123456</td>
                                    <td class="border px-2 py-1">Budi Santoso</td>
                                    <td class="border px-2 py-1 text-right">5000000</td>
                                    <td class="border px-2 py-1 text-right">100000</td>
                                    <td class="border px-2 py-1 text-right">50000</td>
                                    <td class="border px-2 py-1 text-right">0</td>
                                    <td class="border px-2 py-1 text-right">0</td>
                                    <td class="border px-2 py-1 text-right">25000</td>
                                    <td class="border px-2 py-1 text-right">100000</td>
                                    <td class="border px-2 py-1 text-right">4725000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">* Kolom "Gaji Diterima" akan dihitung otomatis jika tidak diisi</p>
                </div>

                <hr class="my-6">

                {{-- Upload Form --}}
                <form action="{{ route('staff.keuangan.salaries.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Bulan *</label>
                            <select name="month" required class="border rounded w-full py-2 px-3 text-gray-700">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tahun *</label>
                            <select name="year" required class="border rounded w-full py-2 px-3 text-gray-700">
                                @for($y = 2024; $y <= 2030; $y++)
                                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">File Excel (.xlsx, .xls) *</label>
                        <input type="file" name="file" accept=".xlsx,.xls" required
                               class="border rounded w-full py-2 px-3 text-gray-700 @error('file') border-red-500 @enderror">
                        @error('file')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Maksimal ukuran file: 5MB</p>
                    </div>

                    <div class="flex gap-3 pt-4 border-t">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded">
                            📥 Import Data
                        </button>
                        <a href="{{ route('staff.keuangan.salaries') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                            Kembali
                        </a>
                    </div>
                </form>
            </div>

            {{-- Info Box --}}
            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded p-4">
                <h4 class="font-bold text-yellow-700 mb-2">⚠️ Catatan Penting</h4>
                <ul class="list-disc list-inside text-sm text-yellow-600 space-y-1">
                    <li>Sistem akan mencari karyawan berdasarkan NIP atau Nama</li>
                    <li>Jika data gaji untuk periode yang sama sudah ada, baris tersebut akan dilewati</li>
                    <li>Data yang berhasil diimport akan berstatus "Draft"</li>
                    <li>Anda dapat mengedit status gaji menjadi "Disetujui" atau "Dibayar" setelah import</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
