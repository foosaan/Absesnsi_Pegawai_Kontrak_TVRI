<x-app-layout title="Master Data PSDM">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900">
                    <i class="fas fa-database text-violet-600 dark:text-violet-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Master Data PSDM</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Kelola data referensi kepegawaian</p>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Kategori Cards --}}
    @if(($types ?? collect())->count() > 0)
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($types as $type)
        <div class="card hover:shadow-lg transition-all group">
            <a href="{{ route('staff.psdm.master-data.show', $type->id) }}" class="block card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-xl bg-violet-100 dark:bg-violet-900/50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-folder-open text-violet-600 dark:text-violet-400"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                                {{ $type->name }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $type->description ?? 'Tidak ada deskripsi' }}
                            </p>
                        </div>
                    </div>
                </div>
            </a>
            <div class="px-5 pb-4 pt-0 flex items-center justify-between">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400 flex-shrink-0">
                    {{ $type->values_count }} nilai
                </span>
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    <i class="fas fa-arrow-right mr-1"></i> Kelola nilai
                </span>
            </div>
        </div>
        @endforeach
    </div>
    @else
    {{-- Empty State --}}
    <div class="card py-16 text-center">
        <div class="flex flex-col items-center justify-center">
            <div class="bg-violet-50 dark:bg-violet-900/20 rounded-full h-20 w-20 flex items-center justify-center mb-5">
                <i class="fas fa-database text-4xl text-violet-300 dark:text-violet-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Belum ada kategori</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm">
                Kategori master data belum dikonfigurasi. Hubungi Admin Sistem.
            </p>
        </div>
    </div>
    @endif
</x-app-layout>
