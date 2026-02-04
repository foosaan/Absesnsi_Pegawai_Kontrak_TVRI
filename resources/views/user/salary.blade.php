<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">💰 Slip Gaji</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if($salaries->count() > 0)
            <div class="space-y-6">
                @foreach($salaries as $salary)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-lg">SLIP GAJI - {{ strtoupper($salary->period) }}</h3>
                                <p class="text-sm text-green-100">TVRI Stasiun D.I. Yogyakarta</p>
                            </div>
                            <a href="{{ route('user.salary.pdf', $salary) }}" 
                               class="bg-white/20 hover:bg-white/30 text-white px-3 py-1 rounded text-sm font-medium transition">
                                📄 Export PDF
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        {{-- Info Karyawan --}}
                        <div class="grid grid-cols-2 gap-4 text-sm mb-6 pb-4 border-b">
                            <div>
                                <span class="text-gray-500">Nama:</span>
                                <span class="font-medium ml-2">{{ Auth::user()->name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">NIP:</span>
                                <span class="font-mono ml-2">{{ Auth::user()->nip ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Penerimaan --}}
                        <div class="mb-4">
                            <h4 class="font-bold text-gray-700 mb-2">PENERIMAAN</h4>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span>Gaji Pokok</span>
                                    <span class="font-mono">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-red-600">
                                    <span>Potongan KPPN</span>
                                    <span class="font-mono">-Rp {{ number_format($salary->potongan_kppn, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-bold pt-2 border-t">
                                    <span>Gaji Bersih</span>
                                    <span class="font-mono">Rp {{ number_format($salary->base_salary - $salary->potongan_kppn, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Potongan Intern --}}
                        <div class="mb-4 p-3 bg-red-50 rounded">
                            <h4 class="font-bold text-red-700 mb-2">POTONGAN INTERN</h4>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span>Simpanan Wajib</span>
                                    <span class="font-mono">Rp {{ number_format($salary->simpanan_wajib, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Kredit Uang</span>
                                    <span class="font-mono">Rp {{ number_format($salary->kredit_uang, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Kredit Toko</span>
                                    <span class="font-mono">Rp {{ number_format($salary->kredit_toko, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Dharma Wanita</span>
                                    <span class="font-mono">Rp {{ number_format($salary->dharma_wanita, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>BPJS</span>
                                    <span class="font-mono">Rp {{ number_format($salary->bpjs, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-bold pt-2 border-t border-red-200">
                                    <span>Jumlah Potongan Intern</span>
                                    <span class="font-mono text-red-600">-Rp {{ number_format($salary->total_potongan_intern, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Gaji Diterima --}}
                        <div class="p-4 bg-green-50 rounded">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-700">JUMLAH GAJI DITERIMA</span>
                                <span class="font-mono font-bold text-2xl text-green-600">Rp {{ number_format($salary->final_salary, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @if($salary->notes)
                        <div class="text-xs text-gray-500 mt-4 pt-3 border-t">
                            <span class="font-medium">Catatan:</span> {{ $salary->notes }}
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            
            @if($salaries->hasPages())
            <div class="mt-4">
                {{ $salaries->links() }}
            </div>
            @endif
            
            @else
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <span class="text-6xl mb-4 block">💰</span>
                <h3 class="font-bold text-lg text-gray-700 mb-2">Belum Ada Data Gaji</h3>
                <p class="text-gray-500">Data gaji akan muncul setelah Staff Keuangan menginput gaji Anda.</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
