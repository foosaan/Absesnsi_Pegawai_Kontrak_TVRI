<x-app-layout title="Kalkulator Gaji">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                    <i class="fas fa-calculator text-purple-600 dark:text-purple-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Kalkulator Gaji</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Hitung simulasi gaji karyawan
                    </p>
                </div>
            </div>
            <a href="{{ route('staff.keuangan.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card dark:card-dark h-fit">
            <div class="card-header dark:card-header-dark">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-params text-blue-500 mr-2"></i>
                    Parameter Perhitungan
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ route('staff.keuangan.calculate.process') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Pilih Karyawan (Opsional)</label>
                        <select name="user_id" id="user_select" class="form-control">
                            <option value="">-- Manual Input --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" 
                                        data-gaji="{{ $user->gaji_pokok }}"
                                        data-jabatan="{{ $user->jabatan }}">
                                    {{ $user->name }} - {{ $user->nip }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih karyawan untuk otomatis mengisi gaji pokok</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gaji Pokok</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="number" name="base_salary" id="base_salary" 
                                   class="form-control pl-10" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tunjangan (Total)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="number" name="allowances" id="allowances" 
                                   class="form-control pl-10" value="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Potongan (Total)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="number" name="deductions" id="deductions" 
                                   class="form-control pl-10" value="0">
                        </div>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mb-6">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <p class="font-medium">Tentang Kalkulator</p>
                                <p>Tools ini hanya untuk simulasi perhitungan. Untuk menyimpan data gaji resmi, gunakan menu Input Gaji.</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-purple w-full justify-center bg-purple-600 text-white hover:bg-purple-700">
                        <i class="fas fa-calculator"></i>
                        <span>Hitung Sekarang</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="hidden lg:block">
            <div class="card dark:card-dark h-full flex flex-col items-center justify-center p-8 text-center text-gray-500 dark:text-gray-400">
                <div class="w-32 h-32 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-chart-pie text-5xl opacity-50"></i>
                </div>
                <h3 class="text-xl font-medium text-gray-700 dark:text-gray-300 mb-2">Hasil Perhitungan</h3>
                <p>Hasil perhitungan gaji akan muncul disini setelah Anda klik tombol Hitung.</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('user_select').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const gaji = selectedOption.getAttribute('data-gaji');
            if (gaji) {
                document.getElementById('base_salary').value = gaji;
            }
        });
    </script>
    @endpush
</x-app-layout>
