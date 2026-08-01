<x-app-layout title="Absen Manual">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                <i class="fas fa-pen-alt text-purple-600 dark:text-purple-400"></i>
            </div>
            <div>
                <h1 class="page-title dark:page-title-dark">Presensi Manual</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Input presensi karyawan secara manual</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit mr-2 text-purple-500"></i>Form Absen Manual</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('staff.psdm.manual-attendance.store') }}">
                    @csrf

                    {{-- Pilih Karyawan --}}
                    <div class="form-group">
                        <label class="form-label">Karyawan <span class="text-red-500">*</span></label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} — {{ $user->jabatan ?? 'Tanpa Jabatan' }}
                                    ({{ $user->attendance_type === 'shift' ? 'Shift' : ($user->attendance_type === 'umum' ? 'Umum' : 'Normal') }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal --}}
                    <div class="form-group">
                        <label class="form-label">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}"
                            class="form-control" required>
                        @error('date')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jam Masuk & Pulang --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Jam Masuk <span class="text-red-500">*</span></label>
                            <input type="time" name="check_in_time" value="{{ old('check_in_time') }}"
                                class="form-control" required>
                            @error('check_in_time')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jam Pulang <span
                                    class="text-gray-400 text-xs">(opsional)</span></label>
                            <input type="time" name="check_out_time" value="{{ old('check_out_time') }}"
                                class="form-control">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Kosongkan jika karyawan akan absen pulang sendiri
                            </p>
                            @error('check_out_time')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Alasan --}}
                    <div class="form-group">
                        <label class="form-label">Alasan <span class="text-red-500">*</span></label>
                        <textarea name="manual_reason" rows="3" class="form-control" required
                            placeholder="Contoh: Lupa absen, GPS error, tugas lapangan, dll.">{{ old('manual_reason') }}</textarea>
                        @error('manual_reason')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Info --}}
                    <div
                        class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg mb-6">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                            <div class="text-sm text-amber-800 dark:text-amber-300">
                                <p class="font-semibold mb-1">Informasi:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Shift akan otomatis terdeteksi berdasarkan jam masuk</li>
                                    <li>Status terlambat dihitung otomatis dari toleransi shift</li>
                                    <li>Absen manual ditandai dengan badge <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">Manual</span>
                                    </li>
                                    <li>Jika jam pulang kosong, karyawan bisa absen pulang sendiri lewat aplikasi</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Absen Manual
                        </button>
                        <a href="{{ route('staff.psdm.monitor') }}" class="btn btn-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>