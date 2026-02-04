<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Detail Gaji: {{ $salary->user->name }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Header Slip -->
            <div class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-t-lg p-6">
                <div class="text-center mb-4">
                    <h3 class="font-bold text-lg">TVRI STASIUN D.I. YOGYAKARTA</h3>
                    <p class="text-green-100 text-sm">SLIP PENERIMAAN GAJI DAN POTONGAN</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-green-100">Periode</p>
                    <p class="font-bold text-lg">{{ strtoupper($salary->period) }}</p>
                </div>
            </div>

            <!-- Info Karyawan -->
            <div class="bg-white shadow p-6 border-b">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Nama:</span>
                        <span class="font-medium ml-2">{{ $salary->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">NIP:</span>
                        <span class="font-mono ml-2">{{ $salary->user->nip ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Golongan:</span>
                        <span class="font-medium ml-2">{{ $salary->user->status_pegawai ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Email:</span>
                        <span class="font-medium ml-2">{{ $salary->user->email }}</span>
                    </div>
                </div>
            </div>

            <!-- PENERIMAAN -->
            <div class="bg-white shadow p-6 border-b">
                <h4 class="font-bold text-green-700 mb-4">💰 PENERIMAAN</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Gaji Pokok</span>
                        <span class="font-mono">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</span>
                    </div>
                    @if($salary->potongan_kppn > 0)
                    <div class="flex justify-between py-2 border-b border-gray-100 text-red-600">
                        <span>Potongan KPPN</span>
                        <span class="font-mono">-Rp {{ number_format($salary->potongan_kppn, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between py-2 bg-green-50 rounded px-2 font-bold">
                        <span class="text-green-700">Gaji Bersih</span>
                        <span class="font-mono text-green-700">Rp {{ number_format($salary->base_salary - $salary->potongan_kppn, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- POTONGAN INTERN -->
            @if($salary->total_potongan_intern > 0)
            <div class="bg-white shadow p-6 border-b">
                <h4 class="font-bold text-red-700 mb-4">📋 POTONGAN INTERN</h4>
                <div class="space-y-2 text-sm">
                    @if($salary->simpanan_wajib > 0)
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Simpanan Wajib</span>
                        <span class="font-mono">Rp {{ number_format($salary->simpanan_wajib, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($salary->kredit_uang > 0)
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Kredit Uang</span>
                        <span class="font-mono">Rp {{ number_format($salary->kredit_uang, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($salary->kredit_toko > 0)
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Kredit Toko</span>
                        <span class="font-mono">Rp {{ number_format($salary->kredit_toko, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($salary->dharma_wanita > 0)
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Dharma Wanita</span>
                        <span class="font-mono">Rp {{ number_format($salary->dharma_wanita, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($salary->bpjs > 0)
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">BPJS</span>
                        <span class="font-mono">Rp {{ number_format($salary->bpjs, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between py-2 bg-red-50 rounded px-2 font-bold">
                        <span class="text-red-700">Jumlah Potongan Intern</span>
                        <span class="font-mono text-red-700">-Rp {{ number_format($salary->total_potongan_intern, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- GAJI DITERIMA -->
            <div class="bg-green-50 shadow p-6 rounded-b-lg">
                <div class="flex justify-between items-center">
                    <span class="font-bold text-lg text-gray-700">JUMLAH GAJI DITERIMA</span>
                    <span class="font-mono font-bold text-2xl text-green-600">Rp {{ number_format($salary->final_salary, 0, ',', '.') }}</span>
                </div>
                @if($salary->notes)
                <div class="mt-4 pt-4 border-t border-green-200 text-sm text-gray-600">
                    <span class="font-medium">Catatan:</span> {{ $salary->notes }}
                </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex gap-3 mt-4">
                <a href="{{ route('staff.keuangan.salaries') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded">
                    ← Kembali
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
