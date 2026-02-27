<x-app-layout title="Log Aktivitas">
    <x-slot name="header">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Log Aktivitas</h1>
    </x-slot>

    {{-- Filter --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="form-field mb-0">
                    <label class="form-label">Aksi</label>
                    <select name="action" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                        <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                        <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                    </select>
                </div>
                <div class="form-field mb-0">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.activity-logs') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Log List --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Aktivitas</h3>
        </div>
        <div class="card-body p-0">
            <ul class="divide-y divide-gray-200 dark:divide-slate-700">
                @forelse($logs as $log)
                <li class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-slate-700/50">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            @if($log->action == 'create')
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                    <i class="fas fa-plus text-green-600 dark:text-green-400"></i>
                                </div>
                            @elseif($log->action == 'update')
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                    <i class="fas fa-edit text-blue-600 dark:text-blue-400"></i>
                                </div>
                            @elseif($log->action == 'delete')
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                                    <i class="fas fa-trash text-red-600 dark:text-red-400"></i>
                                </div>
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                    <i class="fas fa-info text-gray-600 dark:text-gray-400"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                @if($log->action == 'create')
                                    <span class="badge badge-success">Create</span>
                                @elseif($log->action == 'update')
                                    <span class="badge badge-primary">Update</span>
                                @elseif($log->action == 'delete')
                                    <span class="badge badge-danger">Delete</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($log->action) }}</span>
                                @endif
                                <span class="text-sm text-gray-500 dark:text-slate-400">{{ class_basename($log->model_type) }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $log->description }}</p>
                            <div class="mt-2 flex items-center gap-4 text-xs text-gray-500 dark:text-slate-400">
                                <span><i class="fas fa-user mr-1"></i> {{ $log->user->name ?? 'System' }}</span>
                                <span><i class="fas fa-clock mr-1"></i> {{ $log->created_at->format('d M Y H:i') }}</span>
                                @if($log->ip_address)
                                <span><i class="fas fa-globe mr-1"></i> {{ $log->ip_address }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
                @empty
                <li class="px-6 py-10 text-center text-gray-500 dark:text-slate-400">
                    <i class="fas fa-history text-4xl mb-2 opacity-50"></i>
                    <p>Belum ada aktivitas tercatat</p>
                </li>
                @endforelse
            </ul>
        </div>
        
        @if($logs->hasPages())
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
