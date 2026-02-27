<x-app-layout title="Hasil Perhitungan">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900">
                    <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Hasil Perhitungan</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Simulasi gaji karyawan
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('staff.keuangan.calculate') }}" class="btn btn-secondary">
                    <i class="fas fa-calculator"></i>
                    <span>Hitung Ulang</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="card dark:card-dark overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-500 to-green-600 p-6 text-white text-center">
                <p class="text-emerald-100 mb-2 uppercase tracking-wide text-xs font-bold">Total Gaji Bersih</p>
                <h2 class="text-4xl font-bold mb-1">Rp {{ number_format($result['total'], 0, ',', '.') }}</h2>
                <p class="text-emerald-100 text-sm">Estimasi diterima karyawan</p>
            </div>
            
            <div class="card-body p-0">
                <div class="p-6 space-y-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase mb-3">Pemasukan</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700 dark:text-gray-300">Gaji Pokok</span>
                                <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($result['base'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700 dark:text-gray-300">Tunjangan</span>
                                <span class="font-medium text-emerald-600 dark:text-emerald-400">+ Rp {{ number_format($result['allowances'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <hr class="dark:border-slate-700">

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase mb-3">Pengeluaran</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700 dark:text-gray-300">Potongan</span>
                                <span class="font-medium text-red-600 dark:text-red-400">- Rp {{ number_format($result['deductions'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-slate-800 p-6 border-t dark:border-slate-700">
                    <div class="flex gap-4 justify-center">
                        <button onclick="window.print()" class="btn btn-secondary">
                            <i class="fas fa-print"></i>
                            <span>Cetak</span>
                        </button>
                        <a href="{{ route('staff.keuangan.salaries.input') }}" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <span>Input ke Data Gaji</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
