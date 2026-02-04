<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">👥 Kelola Staff</h2>
            <a href="{{ route('admin.staffs.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium">
                + Tambah Staff
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
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
                        <label class="block text-xs font-bold text-gray-600 mb-1">Role</label>
                        <select name="role" class="border rounded px-3 py-2 text-sm">
                            <option value="">Semua Role</option>
                            <option value="staff_psdm" {{ request('role') == 'staff_psdm' ? 'selected' : '' }}>Staff PSDM</option>
                            <option value="staff_keuangan" {{ request('role') == 'staff_keuangan' ? 'selected' : '' }}>Staff Keuangan</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="border rounded px-3 py-2 text-sm w-full" placeholder="Nama atau email...">
                    </div>
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                        🔍 Filter
                    </button>
                    @if(request()->hasAny(['role', 'search']))
                        <a href="{{ route('admin.staffs') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm">Reset</a>
                    @endif
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
                                <th class="px-4 py-3 text-center font-semibold">Role</th>
                                <th class="px-4 py-3 text-center font-semibold">Dibuat</th>
                                <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($staffs as $staff)
                            <tr class="hover:bg-gray-50 {{ $staff->id === auth()->id() ? 'bg-blue-50' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $staff->name }}</div>
                                    @if($staff->id === auth()->id())
                                        <span class="text-xs text-blue-500">(Anda)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $staff->email }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($staff->role === 'admin')
                                        <span class="px-2 py-1 rounded text-xs bg-red-100 text-red-700">Admin</span>
                                    @elseif($staff->role === 'staff_psdm')
                                        <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700">Staff PSDM</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700">Staff Keuangan</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ $staff->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('admin.staffs.edit', $staff) }}" class="text-blue-600 hover:text-blue-800 text-xs">Edit</a>
                                        @if($staff->id !== auth()->id())
                                        <form action="{{ route('admin.staffs.delete', $staff) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Yakin ingin menghapus staff {{ $staff->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">Tidak ada data staff</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($staffs->hasPages())
                <div class="p-4 border-t">
                    {{ $staffs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
