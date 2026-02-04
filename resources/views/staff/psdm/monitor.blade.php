<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Monitor Absensi</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filter -->
            <div class="bg-white rounded-lg shadow mb-4 p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal</label>
                        <input type="date" name="date" value="{{ request('date', now()->format('Y-m-d')) }}" 
                               class="border rounded px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Karyawan</label>
                        <select name="user_id" class="border rounded px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Status</label>
                        <select name="status" class="border rounded px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Hadir</option>
                            <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                        🔍 Filter
                    </button>
                    <a href="{{ route('staff.psdm.monitor') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm">
                        Reset
                    </a>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Karyawan</th>
                                <th class="px-4 py-3 text-center font-semibold">Tipe</th>
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
                                    <div class="font-medium">{{ $attendance->user->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $attendance->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-xs {{ $attendance->user->employee_type === 'ob' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ strtoupper($attendance->user->employee_type ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600">
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
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada data absensi</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($attendances->hasPages())
                <div class="p-4 border-t">
                    {{ $attendances->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
