<x-app-layout title="Data Gaji">
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900">
                    <i class="fas fa-money-bill-wave text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Data Gaji</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') }} {{ $year }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('staff.keuangan.export.salaries', ['month' => $month, 'year' => $year]) }}" class="btn btn-success">
                    <i class="fas fa-file-excel"></i>
                    <span>Export</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Upload Tanda Tangan --}}
    @if(!auth()->user()->signature)
    <div class="card dark:card-dark mb-6 border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-3 flex-1">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/50 shrink-0">
                        <i class="fas fa-signature text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div>
                        <p class="font-medium text-amber-800 dark:text-amber-300">Tanda Tangan Belum Diupload</p>
                        <p class="text-xs text-amber-600 dark:text-amber-400">Upload tanda tangan Anda agar bisa menandatangani slip gaji</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('staff.keuangan.signature.upload') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <input type="file" name="signature" accept="image/*" required
                           class="text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200 dark:file:bg-amber-900/50 dark:file:text-amber-300">
                    <button type="submit" class="btn btn-sm btn-warning shrink-0">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>
    @else
    <div class="card dark:card-dark mb-6 border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-3 flex-1">
                    <img src="{{ asset('storage/' . auth()->user()->signature) }}" class="h-12 w-auto rounded border dark:border-slate-600">
                    <div>
                        <p class="font-medium text-green-800 dark:text-green-300"><i class="fas fa-check-circle mr-1"></i>Tanda Tangan Aktif</p>
                        <p class="text-xs text-green-600 dark:text-green-400">Tanda tangan Anda sudah tersimpan</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('staff.keuangan.signature.upload') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="file" name="signature" accept="image/*" required
                               class="text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-slate-700 dark:file:text-gray-300">
                        <button type="submit" class="btn btn-sm btn-secondary shrink-0">
                            <i class="fas fa-sync-alt mr-1"></i> Ganti
                        </button>
                    </form>
                    {{-- Bulk Sign Button --}}
                    @if($salaries->where('signed_by', null)->count() > 0)
                    <form method="POST" action="{{ route('staff.keuangan.salaries.bulk-sign') }}"
                          data-confirm="Tanda tangani semua slip gaji periode ini yang belum ditandatangani?" 
                          data-confirm-title="Konfirmasi Tanda Tangan Massal">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="btn btn-sm btn-primary shrink-0">
                            <i class="fas fa-file-signature mr-1"></i>
                            TTD Semua ({{ $salaries->where('signed_by', null)->count() }})
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Filter Card --}}
    <div class="card dark:card-dark mb-6">
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('staff.keuangan.salaries') }}" class="flex flex-wrap items-end gap-3">
                <div style="min-width: 150px;">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month((int) $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div style="min-width: 110px;">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-control" onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div style="min-width: 130px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="draft" {{ ($status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ ($status ?? '') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ ($status ?? '') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="flex-1" style="min-width: 200px;">
                    <label class="form-label">Cari Karyawan</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control pl-9" 
                               placeholder="Nama atau NIK...">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Delete Toolbar (hidden by default, shown when items are selected) --}}
    <div id="bulk-toolbar" class="card dark:card-dark mb-4 border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20" style="display: none;">
        <div class="card-body py-3">
            <form id="bulk-delete-form" method="POST" action="{{ route('staff.keuangan.salaries.bulk-delete') }}"
                  data-confirm="Yakin ingin menghapus data gaji yang dipilih?" data-confirm-title="Konfirmasi Hapus Massal">
                @csrf
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-square text-red-500"></i>
                        <span class="text-sm font-medium text-red-700 dark:text-red-300">
                            <span id="selected-count">0</span> data dipilih
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="btn-deselect" class="btn btn-sm btn-secondary">
                            <i class="fas fa-times mr-1"></i> Batal Pilih
                        </button>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash mr-1"></i> Hapus Terpilih
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Salaries Table --}}
    <div class="card dark:card-dark">
        <div class="overflow-x-auto">
            <table class="table dark:table-dark">
                <thead>
                    <tr>
                        <th class="w-10">
                            <input type="checkbox" id="select-all" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700">
                        </th>
                        <th>No</th>
                        <th>Karyawan</th>
                        <th>Gaji Pokok</th>
                        <th>Potongan</th>
                        <th>Gaji Diterima</th>
                        <th>TTD</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="dark:text-gray-300">
                    @forelse($salaries as $index => $salary)
                    <tr>
                        <td>
                            <input type="checkbox" class="salary-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700"
                                   value="{{ $salary->id }}">
                        </td>
                        <td>{{ $salaries->firstItem() + $index }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm bg-blue-600 text-white">
                                    {{ strtoupper(substr($salary->user->name ?? 'N', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $salary->user->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $salary->user->nik ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</td>
                        <td class="text-red-600 dark:text-red-400">
                            - Rp {{ number_format(($salary->potongan_kppn ?? 0) + ($salary->total_potongan_intern ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format($salary->final_salary, 0, ',', '.') }}
                        </td>
                        <td>
                            @if($salary->isSigned())
                                <span class="badge badge-success" title="Ditandatangani oleh {{ $salary->signer->name ?? '-' }} pada {{ $salary->signed_at->format('d/m/Y H:i') }}">
                                    <i class="fas fa-check-circle mr-1"></i>Signed
                                </span>
                            @else
                                @if(auth()->user()->signature)
                                    <form method="POST" action="{{ route('staff.keuangan.salaries.sign', $salary) }}" class="inline"
                                          data-confirm="Tanda tangani slip gaji {{ $salary->user->name ?? '' }}?" data-confirm-title="Konfirmasi TTD">
                                        @csrf
                                        <button type="submit" class="badge bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/50 cursor-pointer transition-colors" title="Klik untuk tanda tangani">
                                            <i class="fas fa-file-signature mr-1"></i>Draft
                                        </button>
                                    </form>
                                @else
                                    <span class="badge badge-warning">Draft</span>
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('staff.keuangan.salaries.show', $salary) }}" 
                                   class="btn btn-sm btn-primary btn-icon" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('staff.keuangan.salaries.edit', $salary) }}" 
                                   class="btn btn-sm btn-warning btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('staff.keuangan.salaries.delete', $salary) }}" method="POST" class="inline"
                                      data-confirm="Yakin ingin menghapus data gaji ini?" data-confirm-title="Konfirmasi Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                            <p>Tidak ada data gaji untuk periode ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($salaries->hasPages())
        <div class="card-body border-t dark:border-slate-700">
            {{ $salaries->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.salary-checkbox');
        const bulkToolbar = document.getElementById('bulk-toolbar');
        const selectedCount = document.getElementById('selected-count');
        const bulkDeleteForm = document.getElementById('bulk-delete-form');
        const btnDeselect = document.getElementById('btn-deselect');

        function updateToolbar() {
            const checked = document.querySelectorAll('.salary-checkbox:checked');
            const count = checked.length;
            selectedCount.textContent = count;
            bulkToolbar.style.display = count > 0 ? 'block' : 'none';

            // Update select-all state
            selectAll.checked = count === checkboxes.length && count > 0;
            selectAll.indeterminate = count > 0 && count < checkboxes.length;

            // Update hidden inputs for bulk delete form
            bulkDeleteForm.querySelectorAll('input[name="salary_ids[]"]').forEach(el => el.remove());
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'salary_ids[]';
                input.value = cb.value;
                bulkDeleteForm.appendChild(input);
            });
        }

        // Select All
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateToolbar();
        });

        // Individual checkboxes
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateToolbar);
        });

        // Deselect button
        btnDeselect.addEventListener('click', function() {
            selectAll.checked = false;
            checkboxes.forEach(cb => cb.checked = false);
            updateToolbar();
        });
    </script>
    @endpush
</x-app-layout>
