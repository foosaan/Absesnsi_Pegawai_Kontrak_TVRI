<x-app-layout title="Detail Gaji - {{ $salary->user->name ?? 'N/A' }}">
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                    <i class="fas fa-file-invoice-dollar text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Detail Gaji</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::create()->month((int) $salary->month)->translatedFormat('F') }} {{ $salary->year }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('staff.keuangan.salaries') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
                <a href="{{ route('staff.keuangan.salaries.edit', $salary) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i>
                    <span>Edit</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Employee Info --}}
            <div class="card dark:card-dark">
                <div class="card-header dark:card-header-dark">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-user text-blue-500 mr-2"></i>
                        Informasi Karyawan
                    </h3>
                </div>
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        <div class="avatar avatar-lg bg-blue-600 text-white flex-shrink-0">
                            {{ strtoupper(substr($salary->user->name ?? 'N', 0, 1)) }}
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 flex-1">
                            <div>
                                <label class="form-label">Nama</label>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $salary->user->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="form-label">NIP</label>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $salary->user->nip ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="form-label">Bagian</label>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $salary->user->bagian ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="form-label">Jabatan</label>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $salary->user->jabatan ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Salary Details --}}
            <div class="card dark:card-dark">
                <div class="card-header dark:card-header-dark">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-money-check-alt text-emerald-500 mr-2"></i>
                        Rincian Gaji
                    </h3>
                </div>
                <div class="card-body">
                    <table class="w-full">
                        <tbody class="divide-y dark:divide-slate-700">
                            <tr>
                                <td class="py-3 text-gray-600 dark:text-gray-400">Gaji Pokok</td>
                                <td class="py-3 text-right font-medium text-gray-900 dark:text-white">
                                    Rp {{ number_format($salary->base_salary, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-600 dark:text-gray-400">Potongan KPPN</td>
                                <td class="py-3 text-right font-medium text-red-600 dark:text-red-400">
                                    - Rp {{ number_format($salary->potongan_kppn ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                            @if($salary->salaryDeductions && $salary->salaryDeductions->count() > 0)
                            <tr>
                                <td colspan="2" class="py-3">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Potongan Intern</span>
                                </td>
                            </tr>
                            @foreach($salary->salaryDeductions as $deduction)
                            <tr>
                                <td class="py-2 pl-4 text-gray-600 dark:text-gray-400 text-sm">
                                    {{ $deduction->type->name ?? 'N/A' }}
                                </td>
                                <td class="py-2 text-right font-medium text-red-600 dark:text-red-400">
                                    - Rp {{ number_format($deduction->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            <tr>
                                <td class="py-3 text-gray-600 dark:text-gray-400">Total Potongan Intern</td>
                                <td class="py-3 text-right font-medium text-red-600 dark:text-red-400">
                                    - Rp {{ number_format($salary->total_potongan_intern ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t-2 dark:border-slate-600">
                            <tr>
                                <td class="py-4 text-lg font-semibold text-gray-900 dark:text-white">Gaji Diterima</td>
                                <td class="py-4 text-right text-xl font-bold text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($salary->final_salary, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Status Card --}}
            <div class="card dark:card-dark">
                <div class="card-header dark:card-header-dark">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Status
                    </h3>
                </div>
                <div class="card-body text-center">
                    @if($salary->status === 'paid')
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900 mb-3">
                            <i class="fas fa-check-circle text-3xl text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                        <p class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">Sudah Dibayar</p>
                    @elseif($salary->status === 'approved')
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900 mb-3">
                            <i class="fas fa-thumbs-up text-3xl text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <p class="text-lg font-semibold text-blue-600 dark:text-blue-400">Disetujui</p>
                    @else
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-900 mb-3">
                            <i class="fas fa-clock text-3xl text-amber-600 dark:text-amber-400"></i>
                        </div>
                        <p class="text-lg font-semibold text-amber-600 dark:text-amber-400">Pending</p>
                    @endif

                    @if($salary->status !== 'paid')
                    <div class="mt-4 space-y-2">
                        <form action="{{ route('staff.keuangan.salaries.status', $salary) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="paid">
                            <button type="submit" class="btn btn-success w-full">
                                <i class="fas fa-check"></i>
                                Tandai Dibayar
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Actions Card --}}
            <div class="card dark:card-dark">
                <div class="card-header dark:card-header-dark">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        <i class="fas fa-cog text-gray-500 mr-2"></i>
                        Aksi
                    </h3>
                </div>
                <div class="card-body space-y-2">
                    <a href="{{ route('staff.keuangan.salaries.edit', $salary) }}" class="btn btn-warning w-full">
                        <i class="fas fa-edit"></i>
                        Edit Data
                    </a>
                    <form action="{{ route('staff.keuangan.salaries.delete', $salary) }}" method="POST"
                          data-confirm="Yakin ingin menghapus data gaji ini?" data-confirm-title="Konfirmasi Hapus">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-full">
                            <i class="fas fa-trash"></i>
                            Hapus Data
                        </button>
                    </form>
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="card dark:card-dark">
                <div class="card-body text-sm text-gray-500 dark:text-gray-400">
                    <p>Dibuat: {{ $salary->created_at?->format('d M Y H:i') ?? '-' }}</p>
                    <p>Diupdate: {{ $salary->updated_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
