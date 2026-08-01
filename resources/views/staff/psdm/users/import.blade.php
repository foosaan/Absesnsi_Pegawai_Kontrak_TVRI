<x-app-layout title="Import Pegawai">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff.psdm.users') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900">
                <i class="fas fa-file-excel text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div>
                <h1 class="page-title dark:page-title-dark">Import Pegawai dari Excel</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Upload file Excel untuk menambah pegawai secara massal</p>
            </div>
        </div>
    </x-slot>

    {{-- Error Alert: Import Gagal --}}
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 rounded-lg">
            <div class="flex items-start gap-3">
                <i class="fas fa-times-circle text-red-500 text-lg mt-0.5"></i>
                <div class="flex-1">
                    <h3 class="font-semibold text-red-700 dark:text-red-400">{{ session('error') }}</h3>
                    @if(session('import_errors'))
                        <ul class="mt-3 space-y-1 text-sm text-red-600 dark:text-red-400 list-disc list-inside max-h-60 overflow-y-auto">
                            @foreach(session('import_errors') as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Format Template --}}
    <div class="card mb-6">
        <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Format File Excel
            </h3>
            <a href="{{ route('staff.psdm.users.template') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-download mr-1"></i> Download Template Excel
            </a>
        </div>
        <div class="card-body">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                File Excel harus memiliki <strong>header di baris pertama</strong> dengan kolom:
            </p>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kolom</th>
                            <th>Wajib</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="dark:text-gray-300">
                        <tr><td><code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">nip</code></td><td><span class="badge badge-danger">Wajib</span></td><td>Nomor Induk Pegawai (unik, harus 18 karakter)</td></tr>
                        <tr><td><code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">nik</code></td><td><span class="badge badge-danger">Wajib</span></td><td>Nomor Induk Kependudukan (unik, harus 16 digit)</td></tr>
                        <tr><td><code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">nama</code></td><td><span class="badge badge-danger">Wajib</span></td><td>Nama lengkap pegawai</td></tr>
                        <tr><td><code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">email</code></td><td><span class="badge badge-danger">Wajib</span></td><td>Email login (unik)</td></tr>
                        <tr><td><code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">no_telepon</code></td><td><span class="badge badge-danger">Wajib</span></td><td>Nomor Telepon/HP (10-13 digit angka)</td></tr>
                        <tr><td><code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">jabatan</code></td><td><span class="badge badge-info">Opsional</span></td><td>Jabatan pegawai</td></tr>
                        <tr><td><code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">bagian</code></td><td><span class="badge badge-info">Opsional</span></td><td>Bagian/divisi</td></tr>
                        <tr><td><code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">status_pegawai</code></td><td><span class="badge badge-info">Opsional</span></td><td>Status pegawai (PNS, Kontrak, dll)</td></tr>
                        <tr><td><code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">jenis_kelamin</code></td><td><span class="badge badge-info">Opsional</span></td><td>L / P atau Laki-laki / Perempuan</td></tr>
                        <tr><td><code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">alamat</code></td><td><span class="badge badge-info">Opsional</span></td><td>Alamat</td></tr>
                        <tr><td><code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">tipe_presensi</code></td><td><span class="badge badge-info">Opsional</span></td><td>normal / shift (default: normal)</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                <p class="text-sm text-amber-700 dark:text-amber-400">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Catatan:</strong> Jika ada <strong>satu saja baris data yang tidak valid</strong>, seluruh proses import akan dibatalkan dan tidak ada data yang disimpan. Perbaiki semua kesalahan terlebih dahulu sebelum mengunggah ulang.
                    Password default: <code class="bg-amber-100 dark:bg-amber-800 px-1 rounded">Presensi@123</code>
                </p>
            </div>
        </div>
    </div>

    {{-- Upload Form --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-upload text-green-500 mr-2"></i>Upload File
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('staff.psdm.users.import.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">File Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv"
                           class="form-control @error('file') border-red-500 @enderror" required>
                    @error('file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload mr-1"></i> Import Pegawai
                    </button>
                    <a href="{{ route('staff.psdm.users') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
