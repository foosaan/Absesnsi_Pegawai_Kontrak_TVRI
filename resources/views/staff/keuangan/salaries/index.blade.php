<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Data Gaji</h2>
            <div class="flex gap-2">
                <a href="{{ route('staff.keuangan.salaries.input') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-medium">
                    📝 Input Manual
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

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

            <!-- Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Karyawan</th>
                                <th class="px-4 py-3 text-right font-semibold">Gaji Pokok</th>
                                <th class="px-4 py-3 text-right font-semibold">Potongan</th>
                                <th class="px-4 py-3 text-right font-semibold">Gaji Diterima</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                                <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($salaries as $salary)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $salary->user->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $salary->user->nip ?? $salary->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-mono">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono text-red-500">
                                    -Rp {{ number_format($salary->deductions, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-green-600">
                                    Rp {{ number_format($salary->final_salary, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($salary->status === 'paid')
                                        <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700">Dibayar</span>
                                    @elseif($salary->status === 'approved')
                                        <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700">Disetujui</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-600">Draft</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('staff.keuangan.salaries.show', $salary) }}" class="text-blue-600 hover:text-blue-800 text-xs">Detail</a>
                                        <form action="{{ route('staff.keuangan.salaries.delete', $salary) }}" method="POST" class="inline" 
                                              onsubmit="return confirm('Yakin ingin menghapus data gaji ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada data gaji untuk periode ini</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($salaries->hasPages())
                <div class="p-4 border-t">
                    {{ $salaries->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
