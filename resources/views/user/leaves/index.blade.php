<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">🏖️ Pengajuan Cuti</h2>
            <a href="{{ route('user.leaves.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium">
                + Ajukan Cuti
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
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

            @if($leaves->count() > 0)
            <div class="space-y-4">
                @foreach($leaves as $leave)
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-600 mr-2">{{ $leave->type_label }}</span>
                            <span class="text-sm text-gray-500">{{ $leave->total_days }} hari</span>
                        </div>
                        @if($leave->status === 'approved')
                            <span class="px-3 py-1 rounded bg-green-100 text-green-700 font-medium text-sm">✅ Disetujui</span>
                        @elseif($leave->status === 'rejected')
                            <span class="px-3 py-1 rounded bg-red-100 text-red-700 font-medium text-sm">❌ Ditolak</span>
                        @else
                            <span class="px-3 py-1 rounded bg-yellow-100 text-yellow-700 font-medium text-sm">⏳ Menunggu</span>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">📅 {{ $leave->start_date->format('d M Y') }}</span>
                            <span class="mx-2">→</span>
                            <span class="font-medium">{{ $leave->end_date->format('d M Y') }}</span>
                        </div>
                    </div>
                    
                    <div class="text-sm text-gray-700 mb-3">
                        <strong>Alasan:</strong> {{ $leave->reason }}
                    </div>
                    
                    @if($leave->status === 'rejected' && $leave->rejection_reason)
                    <div class="bg-red-50 border border-red-200 rounded p-3 text-sm text-red-700">
                        <strong>Alasan Penolakan:</strong> {{ $leave->rejection_reason }}
                    </div>
                    @endif
                    
                    @if($leave->status === 'pending')
                    <div class="mt-3 pt-3 border-t">
                        <form action="{{ route('user.leaves.cancel', $leave) }}" method="POST" class="inline"
                              onsubmit="return confirm('Yakin ingin membatalkan pengajuan cuti ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                Batalkan Pengajuan
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            
            @if($leaves->hasPages())
            <div class="mt-4">
                {{ $leaves->links() }}
            </div>
            @endif
            
            @else
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <span class="text-6xl mb-4 block">🏖️</span>
                <h3 class="font-bold text-lg text-gray-700 mb-2">Belum Ada Pengajuan Cuti</h3>
                <p class="text-gray-500 mb-4">Anda belum pernah mengajukan cuti.</p>
                <a href="{{ route('user.leaves.create') }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                    Ajukan Cuti Sekarang
                </a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
