<x-app-layout title="Bulk Input Gaji">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900">
                    <i class="fas fa-layer-group text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Bulk Input Gaji</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Input gaji untuk banyak karyawan sekaligus
                    </p>
                </div>
            </div>
            <a href="{{ route('staff.keuangan.salaries') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="card dark:card-dark">
        <div class="card-header dark:card-header-dark">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-filter text-blue-500 mr-2"></i>
                Pilih Karyawan
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('staff.keuangan.salaries.bulk') }}" class="flex flex-wrap items-end gap-4">
                <div class="form-group mb-0 min-w-32">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month((int) $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mb-0 min-w-24">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-control" onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mb-0 flex-1 min-w-48">
                    <label class="form-label">Bagian / Divisi</label>
                    <select name="bagian" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Bagian</option>
                        @foreach($bagians ?? [] as $bagian)
                            <option value="{{ $bagian }}" {{ request('bagian') == $bagian ? 'selected' : '' }}>
                                {{ $bagian }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </form>
        </div>
    </div>

    @if($users->count() > 0)
    <form action="{{ route('staff.keuangan.salaries.store.bulk') }}" method="POST" class="mt-6">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">

        <div class="card dark:card-dark mb-6">
            <div class="card-header dark:card-header-dark flex justify-between items-center">
                <h3 class="font-semibold text-gray-900 dark:text-white">Daftar Karyawan</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $users->count() }} karyawan ditemukan
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="table dark:table-dark">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" id="checkAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th>Karyawan</th>
                            <th>Gaji Pokok (Rp)</th>
                            <th>Pot. KPPN (Rp)</th>
                            <th>Pot. Intern (Rp)</th>
                            <th class="text-right">Total Diterima (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="dark:text-gray-300">
                        @foreach($users as $index => $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td>
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                       class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                            </td>
                            <td>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->nip ?? '-' }}</p>
                                </div>
                            </td>
                            <td>
                                <input type="number" name="base_salaries[{{ $user->id }}]" 
                                       class="form-control text-right min-w-32 salary-input" 
                                       data-id="{{ $user->id }}"
                                       value="{{ $user->gaji_pokok ?? 0 }}">
                            </td>
                            <td>
                                <input type="number" name="kppn_cuts[{{ $user->id }}]" 
                                       class="form-control text-right min-w-24 salary-input" 
                                       data-id="{{ $user->id }}"
                                       value="0">
                            </td>
                            <td>
                                <input type="number" name="intern_cuts[{{ $user->id }}]" 
                                       class="form-control text-right min-w-24 salary-input" 
                                       data-id="{{ $user->id }}"
                                       value="0">
                            </td>
                            <td class="text-right font-medium text-gray-900 dark:text-white">
                                <span id="total-{{ $user->id }}">Rp {{ number_format($user->gaji_pokok ?? 0, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 right-0 lg:left-60 bg-white dark:bg-slate-800 border-t dark:border-slate-700 p-4 shadow-lg z-20">
            <div class="flex items-center justify-between max-w-7xl mx-auto px-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Memproses gaji untuk bulan <span class="font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') }} {{ $year }}</span>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i>
                    <span>Simpan Semua Gaji</span>
                </button>
            </div>
        </div>
        {{-- Spacer for fixed bottom bar --}}
        <div class="h-20"></div>
    </form>
    @else
    <div class="card dark:card-dark mt-6">
        <div class="card-body text-center py-12">
            <i class="fas fa-users-slash text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <p class="text-gray-500 dark:text-gray-400">Tidak ada karyawan yang ditemukan untuk kriteria ini.</p>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.getElementById('checkAll').addEventListener('change', function() {
            document.querySelectorAll('.user-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });

        function formatRupiah(num) {
            return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function calculateRow(id) {
            const base = parseInt(document.querySelector(`input[name="base_salaries[${id}]"]`).value) || 0;
            const kppn = parseInt(document.querySelector(`input[name="kppn_cuts[${id}]"]`).value) || 0;
            const intern = parseInt(document.querySelector(`input[name="intern_cuts[${id}]"]`).value) || 0;
            
            const total = base - kppn - intern;
            document.getElementById(`total-${id}`).textContent = formatRupiah(total);
        }

        document.querySelectorAll('.salary-input').forEach(input => {
            input.addEventListener('input', function() {
                calculateRow(this.dataset.id);
            });
        });
    </script>
    @endpush
</x-app-layout>
