<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Data Karyawan</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Search -->
            <div class="bg-white rounded-lg shadow mb-4 p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau NIP..."
                               class="border rounded px-3 py-2 text-sm w-full">
                    </div>
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                        🔍 Cari
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
                                <th class="px-4 py-3 text-left font-semibold">NIP</th>
                                <th class="px-4 py-3 text-left font-semibold">NPWP</th>
                                <th class="px-4 py-3 text-left font-semibold">Bank</th>
                                <th class="px-4 py-3 text-right font-semibold">Gaji Pokok</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                                <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-400">{{ strtoupper($user->employee_type ?? 'N/A') }}</div>
                                </td>
                                <td class="px-4 py-3 font-mono text-sm">{{ $user->nip ?? '-' }}</td>
                                <td class="px-4 py-3 font-mono text-sm">{{ $user->npwp ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($user->nama_bank)
                                        <div class="font-medium">{{ $user->nama_bank }}</div>
                                        <div class="text-xs text-gray-400">{{ $user->nomor_rekening ?? '-' }}</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    @if($user->gaji_pokok)
                                        Rp {{ number_format($user->gaji_pokok, 0, ',', '.') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($user->nip && $user->nomor_rekening && $user->gaji_pokok)
                                        <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700">Lengkap</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-700">Belum Lengkap</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('staff.keuangan.users.edit', $user) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        ✏️ Edit
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada data karyawan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($users->hasPages())
                <div class="p-4 border-t">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
