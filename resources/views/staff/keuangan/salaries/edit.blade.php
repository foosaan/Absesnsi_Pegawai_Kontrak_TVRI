<x-app-layout title="Edit Gaji - {{ $salary->user->name ?? 'N/A' }}">
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900">
                    <i class="fas fa-edit text-amber-600 dark:text-amber-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Edit Data Gaji</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::create()->month((int) $salary->month)->translatedFormat('F') }} {{ $salary->year }}
                    </p>
                </div>
            </div>
            <a href="{{ route('staff.keuangan.salaries.show', $salary) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Form --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('staff.keuangan.salaries.update', $salary) }}" id="editSalaryForm">
                @csrf
                @method('PUT')

                {{-- Employee Info Card --}}
                <div class="card dark:card-dark mb-6">
                    <div class="card-header dark:card-header-dark">
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            <i class="fas fa-user text-blue-500 mr-2"></i>
                            Informasi Karyawan
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="form-label">Nama</label>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $salary->user->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="form-label">NIP</label>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $salary->user->nip ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Salary Input Card --}}
                <div class="card dark:card-dark mb-6">
                    <div class="card-header dark:card-header-dark">
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            <i class="fas fa-money-check-alt text-emerald-500 mr-2"></i>
                            Data Gaji
                        </h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="form-group">
                            <label for="base_salary" class="form-label">Gaji Pokok <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                <input type="number" name="base_salary" id="base_salary" 
                                       class="form-control pl-10" 
                                       value="{{ old('base_salary', $salary->base_salary) }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="potongan_kppn" class="form-label">Potongan KPPN</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                <input type="number" name="potongan_kppn" id="potongan_kppn" 
                                       class="form-control pl-10" 
                                       value="{{ old('potongan_kppn', $salary->potongan_kppn) }}" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Deductions Card --}}
                <div class="card dark:card-dark mb-6">
                    <div class="card-header dark:card-header-dark">
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            <i class="fas fa-minus-circle text-red-500 mr-2"></i>
                            Potongan Intern
                        </h3>
                    </div>
                    <div class="card-body space-y-4">
                        @forelse($deductionTypes ?? [] as $type)
                            @php
                                $existingDeduction = $salary->salaryDeductions->where('deduction_type_id', $type->id)->first();
                                $amount = $existingDeduction ? $existingDeduction->amount : 0;
                            @endphp
                            <div class="form-group">
                                <input type="hidden" name="deduction_ids[]" value="{{ $type->id }}">
                                <label class="form-label">{{ $type->name }}</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                    <input type="number" name="deduction_amounts[]" 
                                           class="form-control pl-10 deduction-input" 
                                           value="{{ $amount }}" min="0">
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400 text-center py-4">
                                Tidak ada jenis potongan. 
                                <a href="{{ route('staff.keuangan.deductions.index') }}" class="text-blue-600 hover:underline">Tambah jenis potongan</a>
                            </p>
                        @endforelse
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-warning btn-lg w-full text-white">
                    <i class="fas fa-save"></i>
                    <span>Update Data Gaji</span>
                </button>
            </form>
        </div>

        {{-- Summary Sidebar --}}
        <div class="lg:col-span-1">
            <div class="card dark:card-dark sticky top-20">
                <div class="card-header dark:card-header-dark">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-calculator text-blue-500 mr-2"></i>
                        Ringkasan
                    </h3>
                </div>
                <div class="card-body space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Periode</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::create()->month((int) $salary->month)->translatedFormat('F') }} {{ $salary->year }}
                        </span>
                    </div>
                    <hr class="dark:border-slate-700">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Gaji Pokok</span>
                        <span class="font-medium text-gray-900 dark:text-white" id="summary-base">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Pot. KPPN</span>
                        <span class="font-medium text-red-600 dark:text-red-400" id="summary-kppn">- Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Pot. Intern</span>
                        <span class="font-medium text-red-600 dark:text-red-400" id="summary-intern">- Rp 0</span>
                    </div>
                    <hr class="dark:border-slate-700">
                    <div class="flex justify-between text-lg">
                        <span class="font-semibold text-gray-900 dark:text-white">Total Diterima</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400" id="summary-total">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function formatRupiah(num) {
            return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function calculate() {
            const base = parseInt(document.getElementById('base_salary').value) || 0;
            const kppn = parseInt(document.getElementById('potongan_kppn').value) || 0;
            
            let intern = 0;
            document.querySelectorAll('.deduction-input').forEach(input => {
                intern += parseInt(input.value) || 0;
            });

            const total = base - kppn - intern;

            document.getElementById('summary-base').textContent = formatRupiah(base);
            document.getElementById('summary-kppn').textContent = '- ' + formatRupiah(kppn);
            document.getElementById('summary-intern').textContent = '- ' + formatRupiah(intern);
            document.getElementById('summary-total').textContent = formatRupiah(total);
        }

        document.getElementById('base_salary').addEventListener('input', calculate);
        document.getElementById('potongan_kppn').addEventListener('input', calculate);
        document.querySelectorAll('.deduction-input').forEach(input => {
            input.addEventListener('input', calculate);
        });

        calculate(); // Initial calculation
    </script>
    @endpush
</x-app-layout>
