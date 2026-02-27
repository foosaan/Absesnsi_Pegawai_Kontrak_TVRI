<x-app-layout title="Dinas Luar">
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                    <i class="fas fa-briefcase text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <h1 class="page-title">Dinas Luar</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Kelola pengajuan dinas luar Anda</p>
                </div>
            </div>
            <a href="{{ route('user.business-trips.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajukan Dinas Luar
            </a>
        </div>
    </x-slot>

    @if($trips->count() > 0)
    <div class="space-y-4">
        @foreach($trips as $trip)
        <div class="card">
            <div class="card-body">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="badge badge-primary"><i class="fas fa-map-marker-alt mr-1"></i>{{ $trip->destination }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $trip->total_days }} hari</span>
                    </div>
                    @if($trip->status === 'approved')
                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Disetujui</span>
                    @elseif($trip->status === 'rejected')
                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Ditolak</span>
                    @else
                        <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Menunggu</span>
                    @endif
                </div>
                
                <div class="mb-3">
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <i class="fas fa-calendar-alt text-blue-500"></i>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $trip->start_date->format('d M Y') }}</span>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $trip->end_date->format('d M Y') }}</span>
                    </div>
                </div>
                
                <div class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                    <strong>Keperluan:</strong> {{ $trip->purpose }}
                </div>
                
                @if($trip->status === 'rejected' && $trip->rejection_reason)
                <div class="notification notification-danger p-3 text-sm">
                    <strong>Alasan Penolakan:</strong> {{ $trip->rejection_reason }}
                </div>
                @endif
                
                @if($trip->status === 'pending')
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-slate-700">
                    <form action="{{ route('user.business-trips.cancel', $trip) }}" method="POST" class="inline"
                          data-confirm="Yakin ingin membatalkan pengajuan dinas luar ini?" data-confirm-title="Konfirmasi Hapus">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-times"></i> Batalkan Pengajuan
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    
    @if(method_exists($trips, 'hasPages') && $trips->hasPages())
    <div class="mt-6">
        {{ $trips->links() }}
    </div>
    @endif
    
    @else
    <div class="card">
        <div class="card-body text-center py-12">
            <i class="fas fa-briefcase text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <h3 class="font-bold text-lg text-gray-700 dark:text-gray-300 mb-2">Belum Ada Pengajuan Dinas Luar</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Anda belum pernah mengajukan dinas luar.</p>
            <a href="{{ route('user.business-trips.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajukan Dinas Luar Sekarang
            </a>
        </div>
    </div>
    @endif
</x-app-layout>
