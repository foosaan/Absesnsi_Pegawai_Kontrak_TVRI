<x-app-layout title="Pengaturan">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                <i class="fas fa-cog text-gray-600 dark:text-gray-400"></i>
            </div>
            <div>
                <h1 class="page-title dark:page-title-dark">Pengaturan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola lokasi absensi dan pengaturan shift</p>
            </div>
        </div>
    </x-slot>


    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Location Settings --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                    Lokasi & Radius Absensi
                </h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Atur titik lokasi kantor dan radius maksimal untuk absensi karyawan.
                </p>

                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
                    @csrf

                    <div class="form-group">
                        <label for="office_latitude" class="form-label dark:text-gray-300">Latitude Kantor</label>
                        <div class="relative">
                            <i class="fas fa-map-pin form-control-icon"></i>
                            <input type="number" step="any" id="office_latitude" name="office_latitude" 
                                   value="{{ $settings['office_latitude'] ?? '' }}" 
                                   class="form-control form-control-with-icon" 
                                   placeholder="-7.795580" required>
                        </div>
                        @error('office_latitude')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="office_longitude" class="form-label dark:text-gray-300">Longitude Kantor</label>
                        <div class="relative">
                            <i class="fas fa-map-pin form-control-icon"></i>
                            <input type="number" step="any" id="office_longitude" name="office_longitude" 
                                   value="{{ $settings['office_longitude'] ?? '' }}" 
                                   class="form-control form-control-with-icon" 
                                   placeholder="110.365470" required>
                        </div>
                        @error('office_longitude')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="allowed_radius_meters" class="form-label dark:text-gray-300">Radius Absensi (meter)</label>
                        <div class="relative">
                            <i class="fas fa-bullseye form-control-icon"></i>
                            <input type="number" id="allowed_radius_meters" name="allowed_radius_meters" 
                                   value="{{ $settings['allowed_radius_meters'] ?? 100 }}" 
                                   class="form-control form-control-with-icon" 
                                   placeholder="100" min="10" required>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Jarak maksimal dari titik kantor untuk bisa absen</p>
                        @error('allowed_radius_meters')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Current Location Info --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    Info Lokasi Saat Ini
                </h3>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Latitude</p>
                            <p class="font-mono font-semibold text-gray-900 dark:text-white">{{ $settings['office_latitude'] ?? 'Belum diatur' }}</p>
                        </div>
                        <i class="fas fa-map-marker-alt text-red-400"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Longitude</p>
                            <p class="font-mono font-semibold text-gray-900 dark:text-white">{{ $settings['office_longitude'] ?? 'Belum diatur' }}</p>
                        </div>
                        <i class="fas fa-map-marker-alt text-blue-400"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Radius Absensi</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $settings['allowed_radius_meters'] ?? '100' }} meter</p>
                        </div>
                        <i class="fas fa-bullseye text-green-400"></i>
                    </div>

                    @if(isset($settings['office_latitude']) && isset($settings['office_longitude']))
                    <a href="https://www.google.com/maps?q={{ $settings['office_latitude'] }},{{ $settings['office_longitude'] }}" 
                       target="_blank" class="btn btn-secondary w-full justify-center">
                        <i class="fas fa-external-link-alt mr-1"></i> Lihat di Google Maps
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Shift Settings --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-clock text-purple-500 mr-2"></i>
                Pengaturan Shift
            </h3>
        </div>
        <div class="card-body">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($shifts as $shift)
                <div class="p-4 rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/30">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">
                        <i class="fas fa-tag text-purple-400 mr-1"></i> {{ $shift->name }}
                    </h4>
                    <form method="POST" action="{{ route('admin.shift.update', $shift) }}" class="space-y-3">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-0">
                            <label class="form-label text-xs dark:text-gray-300">Jam Masuk</label>
                            <input type="time" name="start_time" 
                                   value="{{ $shift->start_time instanceof \Carbon\Carbon ? $shift->start_time->format('H:i') : substr($shift->start_time, 0, 5) }}" 
                                   class="form-control text-sm" required>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label text-xs dark:text-gray-300">Jam Pulang</label>
                            <input type="time" name="end_time" 
                                   value="{{ $shift->end_time instanceof \Carbon\Carbon ? $shift->end_time->format('H:i') : substr($shift->end_time, 0, 5) }}" 
                                   class="form-control text-sm" required>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label text-xs dark:text-gray-300">Toleransi (menit)</label>
                            <input type="number" name="tolerance_minutes" 
                                   value="{{ $shift->tolerance_minutes }}" 
                                   class="form-control text-sm" min="0" max="120" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-full">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Shift Change Logs --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-history text-amber-500 mr-2"></i>
                Log Perubahan Shift
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Shift</th>
                        <th>Field</th>
                        <th>Nilai Lama</th>
                        <th>Nilai Baru</th>
                        <th>Diubah Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shiftLogs as $log)
                        <tr>
                            <td class="text-sm">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $log->shift->name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $log->field_name ?? '-' }}</span>
                            </td>
                            <td class="text-red-500 font-mono text-sm">{{ $log->old_value ?? '-' }}</td>
                            <td class="text-green-500 font-mono text-sm">{{ $log->new_value ?? '-' }}</td>
                            <td>{{ $log->changedByUser->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 dark:text-slate-400 py-8">
                                <i class="fas fa-inbox text-3xl mb-2 opacity-50"></i>
                                <p>Belum ada log perubahan shift</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
