<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Hasil Perhitungan Gaji</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Info Karyawan -->
            <div class="bg-white rounded-lg shadow p-6 mb-4">
                <h3 class="font-bold text-lg mb-3">👤 Informasi Karyawan</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Nama:</span>
                        <span class="font-medium">{{ $user->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Tipe:</span>
                        <span class="px-2 py-0.5 rounded text-xs {{ $user->employee_type === 'ob' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ strtoupper($user->employee_type ?? 'N/A') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">Email:</span>
                        <span class="font-medium">{{ $user->email }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Periode:</span>
                        <span class="font-medium">
                            {{ DateTime::createFromFormat('!m', $calculation['month'])->format('F') }} {{ $calculation['year'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Hasil Kalkulasi -->
            <div class="bg-white rounded-lg shadow p-6 mb-4">
                <h3 class="font-bold text-lg mb-4">📊 Detail Perhitungan</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Hari Kerja (bulan ini)</span>
                        <span class="font-medium">{{ $calculation['total_work_days'] }} hari</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Hadir</span>
                        <span class="font-medium text-green-600">{{ $calculation['days_present'] }} hari</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Terlambat</span>
                        <span class="font-medium text-yellow-600">{{ $calculation['total_late_days'] }} hari</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Tidak Hadir</span>
                        <span class="font-medium text-red-600">{{ $calculation['total_absent_days'] }} hari</span>
                    </div>
                </div>
            </div>

            <!-- Rincian Gaji -->
            <div class="bg-white rounded-lg shadow p-6 mb-4">
                <h3 class="font-bold text-lg mb-4">💰 Rincian Gaji</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Gaji Pokok</span>
                        <span class="font-mono">Rp {{ number_format($calculation['base_salary'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b text-yellow-600">
                        <span>Potongan Terlambat ({{ $calculation['total_late_days'] }} × 2%)</span>
                        <span class="font-mono">- Rp {{ number_format($calculation['late_deduction'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b text-red-600">
                        <span>Potongan Absen ({{ $calculation['total_absent_days'] }} × 4%)</span>
                        <span class="font-mono">- Rp {{ number_format($calculation['absent_deduction'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 bg-gray-50 rounded -mx-2 px-2">
                        <span class="text-gray-600 font-bold">Total Potongan</span>
                        <span class="font-mono text-red-600 font-bold">- Rp {{ number_format($calculation['deductions'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 bg-green-50 rounded -mx-2 px-2">
                        <span class="text-green-700 font-bold text-lg">GAJI AKHIR</span>
                        <span class="font-mono text-green-700 font-bold text-lg">Rp {{ number_format($calculation['final_salary'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('staff.keuangan.salaries.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $calculation['user_id'] }}">
                    <input type="hidden" name="month" value="{{ $calculation['month'] }}">
                    <input type="hidden" name="year" value="{{ $calculation['year'] }}">
                    <input type="hidden" name="base_salary" value="{{ $calculation['base_salary'] }}">
                    <input type="hidden" name="deductions" value="{{ $calculation['deductions'] }}">
                    <input type="hidden" name="total_work_days" value="{{ $calculation['total_work_days'] }}">
                    <input type="hidden" name="total_late_days" value="{{ $calculation['total_late_days'] }}">
                    <input type="hidden" name="total_absent_days" value="{{ $calculation['total_absent_days'] }}">
                    <input type="hidden" name="final_salary" value="{{ $calculation['final_salary'] }}">
                    
                    <div class="flex gap-3">
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded">
                            💾 Simpan Data Gaji
                        </button>
                        <a href="{{ route('staff.keuangan.calculate') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                            🔄 Hitung Ulang
                        </a>
                        <a href="{{ route('staff.keuangan.salaries') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
