<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Kelola User</h2>
            <a href="{{ route('staff.psdm.users.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium">
                + Tambah User
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filter -->
            <div class="bg-white rounded-lg shadow mb-4 p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau email..." 
                               class="border rounded px-3 py-2 text-sm w-48">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Tipe Karyawan</label>
                        <select name="employee_type" class="border rounded px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="ob" {{ request('employee_type') === 'ob' ? 'selected' : '' }}>OB</option>
                            <option value="satpam" {{ request('employee_type') === 'satpam' ? 'selected' : '' }}>Satpam</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                        🔍 Filter
                    </button>
                    <a href="{{ route('staff.psdm.users') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm">
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
                                <th class="px-4 py-3 text-left font-semibold">Nama</th>
                                <th class="px-4 py-3 text-left font-semibold">Email</th>
                                <th class="px-4 py-3 text-center font-semibold">Tipe Karyawan</th>
                                <th class="px-4 py-3 text-center font-semibold">Tipe Absensi</th>
                                <th class="px-4 py-3 text-center font-semibold">Terdaftar</th>
                                <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($user->employee_type)
                                        <span class="px-2 py-1 rounded text-xs font-medium {{ $user->employee_type === 'ob' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }}">
                                            {{ $user->employee_type === 'ob' ? 'Office Boy' : 'Satpam' }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($user->isShiftAttendance())
                                        <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700">🔄 Shift</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">🕐 Normal</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('staff.psdm.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                        <form action="{{ route('staff.psdm.users.delete', $user) }}" method="POST" class="inline" 
                                              onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">Tidak ada data user</td>
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
