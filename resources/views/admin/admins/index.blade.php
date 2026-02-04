<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">🔐 Kelola Admin</h2>
            <a href="{{ route('admin.admins.create') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-medium">
                + Tambah Admin
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
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="border rounded px-3 py-2 text-sm w-full" placeholder="Nama atau email...">
                    </div>
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                        🔍 Filter
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.admins') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm">Reset</a>
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
                                <th class="px-4 py-3 text-center font-semibold">Dibuat</th>
                                <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($admins as $admin)
                            <tr class="hover:bg-gray-50 {{ $admin->id === auth()->id() ? 'bg-blue-50' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $admin->name }}</div>
                                    @if($admin->id === auth()->id())
                                        <span class="text-xs text-blue-500">(Anda)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $admin->email }}</td>
                                <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ $admin->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('admin.admins.edit', $admin) }}" class="text-blue-600 hover:text-blue-800 text-xs">Edit</a>
                                        @if($admin->id !== auth()->id())
                                        <form action="{{ route('admin.admins.delete', $admin) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Yakin ingin menghapus admin {{ $admin->name }}?')">
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
                                <td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada data admin</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($admins->hasPages())
                <div class="p-4 border-t">
                    {{ $admins->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
