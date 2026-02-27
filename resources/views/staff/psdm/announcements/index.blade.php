<x-app-layout title="Pengumuman">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                    <i class="fas fa-bullhorn text-red-600 dark:text-red-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Pengumuman</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Kelola informasi penting untuk pegawai
                    </p>
                </div>
            </div>
            <a href="{{ route('staff.psdm.announcements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                <span>Buat Pengumuman</span>
            </a>
        </div>
    </x-slot>

    <div class="grid gap-6">
        @forelse($announcements as $announcement)
        <div class="card dark:card-dark {{ !$announcement->is_active ? 'opacity-75' : '' }}">
            <div class="card-body">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if(!$announcement->is_active)
                                <span class="badge badge-secondary">Non-Aktif</span>
                            @endif
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-white">
                                {{ $announcement->title }}
                            </h3>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                            Diposting oleh <span class="font-medium text-gray-700 dark:text-gray-300">{{ $announcement->creator->name ?? 'Admin' }}</span>
                            &bull; {{ $announcement->created_at->diffForHumans() }}
                        </p>
                        <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 text-sm">
                            {!! nl2br(e($announcement->content)) !!}
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('staff.psdm.announcements.edit', $announcement) }}" 
                           class="btn btn-sm btn-secondary btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('staff.psdm.announcements.toggle', $announcement) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $announcement->is_active ? 'btn-warning' : 'btn-success' }} btn-icon"
                                    title="{{ $announcement->is_active ? 'Non-aktifkan' : 'Aktifkan' }}">
                                <i class="fas {{ $announcement->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('staff.psdm.announcements.delete', $announcement) }}" method="POST"
                              data-confirm="Hapus pengumuman ini?" data-confirm-title="Konfirmasi Hapus">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card dark:card-dark py-12 text-center">
            <div class="flex flex-col items-center justify-center">
                <div class="bg-gray-100 dark:bg-slate-700 rounded-full h-16 w-16 flex items-center justify-center mb-4">
                    <i class="fas fa-bullhorn text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Belum ada pengumuman</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-sm mt-1">
                    Buat pengumuman baru untuk memberitahu informasi penting kepada pegawai.
                </p>
                <a href="{{ route('staff.psdm.announcements.create') }}" class="btn btn-primary mt-4">
                    <i class="fas fa-plus"></i> Buat Pengumuman
                </a>
            </div>
        </div>
        @endforelse

        <div class="mt-4">
            {{ $announcements->links() }}
        </div>
    </div>
</x-app-layout>
