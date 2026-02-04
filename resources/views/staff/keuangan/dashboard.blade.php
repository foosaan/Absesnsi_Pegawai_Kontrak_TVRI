<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Dashboard Staff Keuangan</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Statistik Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="text-3xl font-bold text-blue-600">{{ $totalUsers }}</div>
                    <div class="text-gray-500 text-sm">Total Karyawan</div>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="text-3xl font-bold text-green-600">{{ $salariesThisMonth }}</div>
                    <div class="text-gray-500 text-sm">Gaji Sudah Dihitung (Bulan Ini)</div>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="text-3xl font-bold text-yellow-600">{{ $pendingSalaries }}</div>
                    <div class="text-gray-500 text-sm">Belum Dihitung</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quick Actions -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Menu Cepat -->
                    <div class="bg-white rounded-lg shadow p-5">
                        <h3 class="font-bold text-lg mb-4">🚀 Menu Cepat</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <a href="{{ route('staff.keuangan.salaries') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                <span class="text-2xl mb-2">💰</span>
                                <span class="text-sm font-medium text-center">Lihat Gaji</span>
                            </a>
                            <a href="{{ route('staff.keuangan.calculate') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                                <span class="text-2xl mb-2">🧮</span>
                                <span class="text-sm font-medium text-center">Hitung Gaji</span>
                            </a>
                            <a href="{{ route('staff.keuangan.salaries', ['month' => $currentMonth, 'year' => $currentYear]) }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                                <span class="text-2xl mb-2">📊</span>
                                <span class="text-sm font-medium text-center">Rekap Bulanan</span>
                            </a>
                        </div>
                    </div>

                    <!-- Gaji Terbaru -->
                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg">📋 Gaji Terbaru</h3>
                            <a href="{{ route('staff.keuangan.salaries') }}" class="text-blue-600 text-sm hover:underline">Lihat Semua →</a>
                        </div>
                        
                        @if($recentSalaries->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold">Karyawan</th>
                                        <th class="px-3 py-2 text-center font-semibold">Periode</th>
                                        <th class="px-3 py-2 text-right font-semibold">Gaji Akhir</th>
                                        <th class="px-3 py-2 text-center font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($recentSalaries as $salary)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2">{{ $salary->user->name }}</td>
                                        <td class="px-3 py-2 text-center">{{ $salary->period }}</td>
                                        <td class="px-3 py-2 text-right font-mono">Rp {{ number_format($salary->final_salary, 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-center">
                                            @if($salary->status === 'paid')
                                                <span class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-700">Dibayar</span>
                                            @elseif($salary->status === 'approved')
                                                <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700">Disetujui</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">Draft</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-gray-400 text-center py-8">Belum ada data gaji</p>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-5 text-white">
                        <h3 class="font-bold text-lg mb-2">💡 Tips</h3>
                        <ul class="text-sm space-y-2 opacity-90">
                            <li>• Hitung gaji di akhir bulan</li>
                            <li>• Pastikan semua absensi sudah terekam</li>
                            <li>• Potongan: Terlambat 2%, Absen 4%</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
