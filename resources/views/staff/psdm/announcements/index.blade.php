<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Pengumuman</h2>
            <a href="{{ route('staff.psdm.announcements.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium">
                + Tambah Pengumuman
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-lg shadow">
                @forelse($announcements as $announcement)
                <div class="p-5 border-b last:border-b-0 {{ !$announcement->is_active ? 'bg-gray-50 opacity-60' : '' }}">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-lg">{{ $announcement->title }}</h3>
                                @if(!$announcement->is_active)
                                    <span class="px-2 py-0.5 bg-gray-200 text-gray-600 text-xs rounded">Non-aktif</span>
                                @else
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded">Aktif</span>
                                @endif
                            </div>
                            <p class="text-gray-600 text-sm mb-2">{{ $announcement->content }}</p>
                            <p class="text-xs text-gray-400">
                                Oleh: {{ $announcement->creator->name ?? 'Unknown' }} • 
                                {{ $announcement->created_at->format('d M Y H:i') }}
                            </p>
                        </div>
                        <div class="flex gap-2 ml-4">
                            <form action="{{ route('staff.psdm.announcements.toggle', $announcement) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-sm px-3 py-1 rounded {{ $announcement->is_active ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                    {{ $announcement->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form action="{{ route('staff.psdm.announcements.delete', $announcement) }}" method="POST" 
                                  onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm px-3 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400">
                    Belum ada pengumuman
                </div>
                @endforelse
                
                @if($announcements->hasPages())
                <div class="p-4 border-t">
                    {{ $announcements->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
