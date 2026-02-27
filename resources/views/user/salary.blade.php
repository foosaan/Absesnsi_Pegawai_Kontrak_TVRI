<x-app-layout title="Slip Gaji">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900">
                <i class="fas fa-money-bill-wave text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div>
                <h1 class="page-title">Slip Gaji</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Riwayat gaji Anda</p>
            </div>
        </div>
    </x-slot>

    @if($salaries->count() > 0)
    <div class="card" x-data="{ openId: null }">
        <div class="card-header flex items-center justify-between">
            <h3 class="card-title flex items-center gap-2">
                <i class="fas fa-list-alt text-emerald-500 text-sm"></i>
                Daftar Slip Gaji
            </h3>
            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $salaries->count() }} slip</span>
        </div>
        <div class="card-body p-0">
            <div class="divide-y divide-gray-100 dark:divide-slate-700">
                @foreach($salaries as $salary)
                <div class="group">
                    {{-- Compact Row --}}
                    <button @click="openId = openId === {{ $salary->id }} ? null : {{ $salary->id }}" 
                            class="w-full px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors text-left">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0"
                                 :class="openId === {{ $salary->id }} ? 'bg-emerald-100 dark:bg-emerald-900/50' : 'bg-gray-100 dark:bg-slate-700'">
                                <i class="fas fa-file-invoice-dollar text-sm"
                                   :class="openId === {{ $salary->id }} ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500'"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $salary->period }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                    Gaji Pokok: Rp {{ number_format($salary->base_salary, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($salary->final_salary, 0, ',', '.') }}</p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500">Diterima</p>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200"
                               :class="openId === {{ $salary->id }} ? 'rotate-180' : ''"></i>
                        </div>
                    </button>

                    {{-- Expanded Detail --}}
                    <div x-show="openId === {{ $salary->id }}" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         x-cloak>
                        <div class="px-5 pb-5 pt-0">
                            <div class="bg-gray-50 dark:bg-slate-800/50 rounded-xl p-5 space-y-5 border border-gray-100 dark:border-slate-700">
                                {{-- Header --}}
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 pb-4 border-b border-gray-200 dark:border-slate-600">
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white">SLIP GAJI - {{ strtoupper($salary->period) }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">TVRI Stasiun D.I. Yogyakarta</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($salary->isSigned())
                                            <span class="badge badge-success text-xs"><i class="fas fa-check-circle mr-1"></i>Signed</span>
                                            <a href="{{ route('user.salary.pdf', $salary) }}" class="btn btn-sm btn-success inline-flex items-center gap-1.5">
                                                <i class="fas fa-file-pdf text-xs"></i> Export PDF
                                            </a>
                                        @else
                                            <span class="badge badge-warning text-xs"><i class="fas fa-clock mr-1"></i>Belum TTD</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Info Karyawan --}}
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Nama:</span>
                                        <span class="font-medium text-gray-900 dark:text-white ml-1">{{ Auth::user()->name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">NIP:</span>
                                        <span class="font-mono text-gray-900 dark:text-white ml-1">{{ Auth::user()->nip ?? '-' }}</span>
                                    </div>
                                </div>

                                {{-- Penerimaan --}}
                                <div>
                                    <h5 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Penerimaan</h5>
                                    <div class="space-y-1.5 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Gaji Pokok</span>
                                            <span class="font-mono text-gray-900 dark:text-white">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between text-red-600 dark:text-red-400">
                                            <span>Potongan KPPN</span>
                                            <span class="font-mono">-Rp {{ number_format($salary->potongan_kppn, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between font-bold pt-1.5 border-t border-gray-200 dark:border-slate-600">
                                            <span class="text-gray-900 dark:text-white">Gaji Bersih</span>
                                            <span class="font-mono text-gray-900 dark:text-white">Rp {{ number_format($salary->base_salary - $salary->potongan_kppn, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Potongan Intern --}}
                                <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                    <h5 class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wider mb-2">Potongan Intern</h5>
                                    <div class="space-y-1.5 text-sm">
                                        @foreach($deductionTypes as $type)
                                        @php
                                            $deduction = $salary->salaryDeductions->firstWhere('deduction_type_id', $type->id);
                                            $amount = $deduction ? $deduction->amount : 0;
                                        @endphp
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">{{ $type->name }}</span>
                                            <span class="font-mono text-gray-900 dark:text-white">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                        </div>
                                        @endforeach
                                        <div class="flex justify-between font-bold pt-1.5 border-t border-red-200 dark:border-red-800">
                                            <span class="text-red-700 dark:text-red-400">Jumlah Potongan</span>
                                            <span class="font-mono text-red-600 dark:text-red-400">-Rp {{ number_format($salary->total_potongan_intern, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Gaji Diterima --}}
                                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                        <span class="font-bold text-gray-700 dark:text-gray-300 text-sm">JUMLAH GAJI DITERIMA</span>
                                        <span class="font-mono font-bold text-xl text-emerald-600 dark:text-emerald-400">Rp {{ number_format($salary->final_salary, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                @if($salary->notes)
                                <div class="text-sm text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-200 dark:border-slate-600">
                                    <span class="font-medium">Catatan:</span> {{ $salary->notes }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @if(method_exists($salaries, 'hasPages') && $salaries->hasPages())
    <div class="mt-6">
        {{ $salaries->links() }}
    </div>
    @endif

    @else
    <div class="card">
        <div class="card-body text-center py-12">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-700 mb-4">
                <i class="fas fa-money-bill-wave text-2xl text-gray-400 dark:text-gray-500"></i>
            </div>
            <h3 class="font-bold text-lg text-gray-700 dark:text-gray-300 mb-2">Belum Ada Data Gaji</h3>
            <p class="text-gray-500 dark:text-gray-400">Data gaji akan muncul setelah Staff Keuangan menginput gaji Anda.</p>
        </div>
    </div>
    @endif
</x-app-layout>
