<x-app-layout title="Import Gaji">
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900">
                    <i class="fas fa-file-import text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div>
                    <h1 class="page-title dark:page-title-dark">Import Data Gaji</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Upload file Excel untuk import data gaji massal
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('staff.keuangan.salaries.template') }}" class="btn btn-primary" target="_blank">
                    <i class="fas fa-download"></i>
                    <span>Download Template</span>
                </a>
                <a href="{{ route('staff.keuangan.salaries') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </x-slot>

    @if($errors->any())
        <div class="notification notification-danger mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="notification notification-danger mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="notification notification-success mb-4">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('import_errors'))
        <div class="card dark:card-dark mb-4">
            <div class="card-header dark:card-header-dark bg-red-50 dark:bg-red-900/20">
                <h3 class="font-semibold text-red-700 dark:text-red-400">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Detail Error Import
                </h3>
            </div>
            <div class="card-body max-h-60 overflow-y-auto">
                <ul class="text-sm space-y-1 text-red-600 dark:text-red-400">
                    @foreach(session('import_errors') as $err)
                        <li><i class="fas fa-times mr-1"></i>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Import Form --}}
        <div class="card dark:card-dark h-fit">
            <div class="card-header dark:card-header-dark">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-cloud-upload-alt text-blue-500 mr-2"></i>
                    Form Upload
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ route('staff.keuangan.salaries.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="form-group">
                            <label class="form-label">Bulan</label>
                            <select name="month" class="form-control">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month((int) $m)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun</label>
                            <select name="year" class="form-control">
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-6">
                        <label class="form-label">File Excel</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-slate-600 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-file-excel text-4xl text-gray-400"></i>
                                <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                    <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                        <span>Upload file</span>
                                        <input id="file-upload" name="file" type="file" class="sr-only" accept=".xlsx,.xls,.csv">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    XLSX atau XLS hingga 10MB
                                </p>
                            </div>
                        </div>
                        <p id="file-name" class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center"></p>
                    </div>

                    <div class="flex items-center gap-2 mb-4">
                        <input type="checkbox" name="overwrite" id="overwrite" value="1"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700">
                        <label for="overwrite" class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-medium">Timpa data yang sudah ada</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 block">Jika dicentang, data gaji yang sudah ada untuk periode ini akan ditimpa</span>
                        </label>
                    </div>

                    <button type="submit" id="btn-import" class="btn btn-success w-full">
                        <i class="fas fa-upload" id="btn-icon"></i>
                        <span id="btn-text">Import Data</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Instructions --}}
        <div class="card dark:card-dark h-fit">
            <div class="card-header dark:card-header-dark">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-info-circle text-amber-500 mr-2"></i>
                    Petunjuk Import
                </h3>
            </div>
            <div class="card-body">
                <div class="prose dark:prose-invert text-sm text-gray-600 dark:text-gray-300">
                    <p>Upload file Excel gaji untuk import data otomatis:</p>
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Siapkan file <strong>Excel gaji</strong> atau gunakan template dari tombol <strong>Download Template</strong>.</li>
                        <li>Sistem akan <strong>mendeteksi header otomatis</strong> berdasarkan nama kolom:
                            <ul class="list-disc list-inside ml-4 mt-1 text-xs">
                                <li><strong>NIK / NIP / PNIP</strong> → Identitas karyawan (dicari berdasarkan NIK)</li>
                                <li><strong>NMPPNPN / NAMA / NMPNG</strong> → Nama </li>
                                <li><strong>GAJI POKOK / PENGHASILAN</strong> → Gaji pokok</li>
                                <li><strong>POTONGAN / PPH</strong> → Potongan KPPN</li>
                                <li><strong>Potongan Intern</strong> → Otomatis dideteksi jika ada kolom sesuai jenis potongan (misal: Simpanan Wajib, Kredit Uang, dll)</li>
                                <li><strong>GAJI DITERIMA / NITERIMA</strong> → Opsional, jika kosong dihitung otomatis</li>
                            </ul>
                        </li>
                        <li>Pastikan <strong>NIK</strong> karyawan sudah terdaftar di sistem (menu PSDM).</li>
                        <li>Pilih <strong>Bulan</strong> dan <strong>Tahun</strong> periode gaji.</li>
                        <li>Upload file pada form di samping.</li>
                        <li>Klik <strong>Import Data</strong> untuk memproses.</li>
                    </ol>

                    <div class="alert alert-warning mt-4">
                        <div class="flex gap-2">
                            <i class="fas fa-exclamation-triangle mt-1"></i>
                            <div>
                                <span class="font-semibold">Perhatian:</span>
                                <p>Jika data gaji untuk periode yang sama sudah ada, data tersebut akan di-skip. Centang <strong>"Timpa data yang sudah ada"</strong> jika ingin menimpa data lama.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const fileInput = document.getElementById('file-upload');
        const fileNamePara = document.getElementById('file-name');
        const importForm = document.querySelector('form[action*="import"]');
        const btnImport = document.getElementById('btn-import');
        const btnIcon = document.getElementById('btn-icon');
        const btnText = document.getElementById('btn-text');

        // Tampilkan nama file yang dipilih
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileNamePara.textContent = 'File terpilih: ' + this.files[0].name;
            } else {
                fileNamePara.textContent = '';
            }
        });

        // Loading state saat submit
        importForm.addEventListener('submit', function() {
            btnImport.disabled = true;
            btnImport.classList.add('opacity-75', 'cursor-not-allowed');
            btnIcon.className = 'fas fa-spinner fa-spin';
            btnText.textContent = 'Memproses...';
        });
    </script>
    @endpush
</x-app-layout>
