<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">📊 Rekap Absensi</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filter -->
            <div class="bg-white rounded-lg shadow mb-4 p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Bulan</label>
                        <select name="month" class="border rounded px-3 py-2 text-sm">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Tahun</label>
                        <select name="year" class="border rounded px-3 py-2 text-sm">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                        🔍 Filter
                    </button>
                </form>
            </div>

            <!-- Statistik -->
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total_hadir'] }}</div>
                    <div class="text-xs text-gray-500">Total Hadir</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['total_tepat_waktu'] }}</div>
                    <div class="text-xs text-gray-500">Tepat Waktu</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['total_terlambat'] }}</div>
                    <div class="text-xs text-gray-500">Terlambat</div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                                <th class="px-4 py-3 text-center font-semibold">Shift</th>
                                <th class="px-4 py-3 text-center font-semibold">Masuk</th>
                                <th class="px-4 py-3 text-center font-semibold">Pulang</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($attendances as $attendance)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $attendance->check_in_time->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $attendance->check_in_time->translatedFormat('l') }}</div>
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600 text-xs">
                                    {{ $attendance->shift->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono">
                                    🟢 {{ $attendance->check_in_time->format('H:i') }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono">
                                    @if($attendance->check_out_time)
                                        🔴 {{ $attendance->check_out_time->format('H:i') }}
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($attendance->status === 'late')
                                        <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-700">⚠️ Terlambat</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700">✅ Hadir</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">Tidak ada data absensi untuk periode ini</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
