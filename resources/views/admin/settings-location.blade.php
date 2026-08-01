<x-app-layout title="Pengaturan Lokasi">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                <i class="fas fa-map-marker-alt text-red-600 dark:text-red-400"></i>
            </div>
            <div>
                <h1 class="page-title dark:page-title-dark">Pengaturan Lokasi</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Atur titik lokasi kantor dan radius presensi</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Location Settings --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                    Lokasi & Radius Presensi
                </h3>
                <button type="button" id="btn-edit-location" class="btn btn-sm btn-secondary" onclick="toggleEditLocation()">
                    <i class="fas fa-edit mr-1"></i> Edit
                </button>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.location.update') }}" id="form-location" class="space-y-4">
                    @csrf

                    <div class="form-group">
                        <label for="office_latitude" class="form-label dark:text-gray-300">Latitude Kantor</label>
                        <div class="relative">
                            <i class="fas fa-map-pin form-control-icon"></i>
                            <input type="number" step="any" id="office_latitude" name="office_latitude" 
                                   value="{{ $settings['office_latitude'] ?? '' }}" 
                                   class="form-control form-control-with-icon" 
                                   placeholder="-7.795580" required disabled>
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
                                   placeholder="110.365470" required disabled>
                        </div>
                        @error('office_longitude')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="allowed_radius_meters" class="form-label dark:text-gray-300">Radius Presensi (meter)</label>
                        <div class="relative">
                            <i class="fas fa-bullseye form-control-icon"></i>
                            <input type="number" id="allowed_radius_meters" name="allowed_radius_meters" 
                                   value="{{ $settings['allowed_radius_meters'] ?? 100 }}" 
                                   class="form-control form-control-with-icon" 
                                   placeholder="100" min="10" required disabled>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Jarak maksimal dari titik kantor untuk bisa absen</p>
                        @error('allowed_radius_meters')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 hidden" id="btn-save-location">
                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="cancelEditLocation()">
                                Batal
                            </button>
                        </div>
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
                            <p class="text-xs text-gray-500 dark:text-gray-400">Radius Presensi</p>
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

    <script>
        const locationInputs = document.querySelectorAll('#form-location input');
        const btnEdit = document.getElementById('btn-edit-location');
        const btnSave = document.getElementById('btn-save-location');

        // Simpan nilai awal untuk rollback saat batal
        let originalValues = {};
        locationInputs.forEach(input => {
            originalValues[input.id] = input.value;
        });

        function toggleEditLocation() {
            locationInputs.forEach(input => input.disabled = false);
            btnSave.classList.remove('hidden');
            btnEdit.classList.add('hidden');
        }

        function cancelEditLocation() {
            locationInputs.forEach(input => {
                input.disabled = true;
                input.value = originalValues[input.id] || '';
            });
            btnSave.classList.add('hidden');
            btnEdit.classList.remove('hidden');
        }
    </script>

</x-app-layout>
