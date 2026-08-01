<x-app-layout title="Pengaturan">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                <i class="fas fa-cog text-gray-600 dark:text-gray-400"></i>
            </div>
            <div>
                <h1 class="page-title dark:page-title-dark">Pengaturan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola lokasi presensi, radius, dan pengaturan shift kerja</p>
            </div>
        </div>
    </x-slot>

    {{-- Leaflet Assets --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        #map-container {
            height: 420px;
            width: 100%;
            z-index: 10;
        }
        /* Leaflet Dark Mode Filter Override */
        .dark .leaflet-tile {
            filter: brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3);
        }
        .dark .leaflet-container {
            background: #111827;
        }
        .dark .leaflet-bar a {
            background-color: #1f2937;
            color: #f3f4f6;
            border-bottom: 1px solid #374151;
        }
        .dark .leaflet-bar a:hover {
            background-color: #374151;
        }
    </style>

    {{-- Tab Navigation --}}
    <div class="border-b border-gray-200 dark:border-slate-700 mb-6">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button onclick="switchTab('location')" id="tab-btn-location" 
                    class="tab-btn pb-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-all">
                <i class="fas fa-map-marker-alt text-base"></i>
                Lokasi & Radius Presensi
            </button>
            <button onclick="switchTab('shift')" id="tab-btn-shift" 
                    class="tab-btn pb-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-all">
                <i class="fas fa-clock text-base"></i>
                Pengaturan Shift
            </button>
            <button onclick="switchTab('logs')" id="tab-btn-logs" 
                    class="tab-btn pb-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-all">
                <i class="fas fa-history text-base"></i>
                Log Perubahan Shift
            </button>
        </nav>
    </div>

    {{-- Tab 1: Location Settings --}}
    <div id="tab-content-location" class="tab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Form and Info Column --}}
            <div class="lg:col-span-5 space-y-6">
                <div class="card dark:card-dark">
                    <div class="card-header dark:card-header-dark">
                        <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-sliders-h text-red-500"></i>
                            Konfigurasi Lokasi
                        </h3>
                    </div>
                    <div class="card-body">
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
                                <label for="allowed_radius_meters" class="form-label dark:text-gray-300">Radius Presensi (meter)</label>
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
                                <button type="submit" class="btn btn-primary w-full justify-center">
                                    <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Last Updater Info --}}
                <div class="card dark:card-dark">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white text-sm">Riwayat Pembaruan</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    @if($updater)
                                        Diperbarui oleh <strong>{{ $updater->name }}</strong> pada {{ \Carbon\Carbon::parse($updatedAt)->format('d M Y H:i') }} WIB.
                                    @else
                                        Belum ada riwayat pembaruan tercatat.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Map Column --}}
            <div class="lg:col-span-7">
                <div class="card dark:card-dark h-full">
                    <div class="card-header dark:card-header-dark flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-map text-blue-500"></i>
                            Visualisasi Peta & Radius Kantor
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Klik / Geser pin untuk ubah lokasi</p>
                    </div>
                    <div class="card-body p-0">
                        <div id="map-container" class="rounded-b-xl"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab 2: Shift Settings --}}
    <div id="tab-content-shift" class="tab-content hidden">
        <div class="card dark:card-dark mb-6">
            <div class="card-header dark:card-header-dark">
                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-clock text-purple-500"></i>
                    Konfigurasi Shift Kerja
                </h3>
            </div>
            <div class="card-body">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($shifts as $shift)
                    <div class="p-5 rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/30">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            {{ $shift->name }}
                        </h4>
                        <form method="POST" action="{{ route('admin.shift.update', $shift) }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label class="form-label text-xs dark:text-gray-300">Jam Masuk</label>
                                <input type="time" name="start_time" 
                                       value="{{ $shift->start_time instanceof \Carbon\Carbon ? $shift->start_time->format('H:i') : substr($shift->start_time, 0, 5) }}" 
                                       class="form-control text-sm" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label text-xs dark:text-gray-300">Jam Pulang</label>
                                <input type="time" name="end_time" 
                                       value="{{ old('end_time', $shift->end_time instanceof \Carbon\Carbon ? $shift->end_time->format('H:i') : substr($shift->end_time, 0, 5)) }}" 
                                       class="form-control text-sm @error('end_time') border-red-500 @enderror" required>
                                @error('end_time')
                                    <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label text-xs dark:text-gray-300">Toleransi (menit)</label>
                                <input type="number" name="tolerance_minutes" 
                                       value="{{ $shift->tolerance_minutes }}" 
                                       class="form-control text-sm" min="0" max="120" required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm w-full justify-center mt-2">
                                <i class="fas fa-save mr-1"></i> Simpan Shift
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Tab 3: Shift Change Logs --}}
    <div id="tab-content-logs" class="tab-content hidden">
        <div class="card dark:card-dark">
            <div class="card-header dark:card-header-dark">
                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-history text-amber-500"></i>
                    Log Perubahan Konfigurasi Shift
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
                                <td class="text-sm">{{ $log->created_at->format('d M Y H:i') }} WIB</td>
                                <td class="font-semibold">{{ $log->shift->name ?? '-' }}</td>
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
    </div>

    @push('scripts')
    <script>
        // Tab switching logic
        function switchTab(tabId) {
            // Hide all tab content
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            
            // Show selected tab content
            document.getElementById('tab-content-' + tabId).classList.remove('hidden');
            
            // Reset tab button states
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.className = "tab-btn pb-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 transition-all";
            });
            
            // Set active button state
            const activeBtn = document.getElementById('tab-btn-' + tabId);
            activeBtn.className = "tab-btn pb-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 border-red-500 text-red-600 dark:text-red-400 transition-all";

            // Save state to localStorage
            localStorage.setItem('active_settings_tab', tabId);

            // Leaflet size validation bug fix
            if (tabId === 'location' && window.map) {
                setTimeout(() => {
                    window.map.invalidateSize();
                }, 150);
            }
        }

        // Initialize active tab from localStorage or default
        document.addEventListener('DOMContentLoaded', () => {
            const savedTab = localStorage.getItem('active_settings_tab') || 'location';
            switchTab(savedTab);
            initLeafletMap();
        });

        // Compatibility for Turbo transitions
        document.addEventListener('turbo:load', () => {
            const savedTab = localStorage.getItem('active_settings_tab') || 'location';
            switchTab(savedTab);
            initLeafletMap();
        });

        // Leaflet Map Initialization
        function initLeafletMap() {
            const mapEl = document.getElementById('map-container');
            if (!mapEl) return;

            // Wait until Leaflet is fully loaded
            if (typeof L === 'undefined') {
                setTimeout(initLeafletMap, 100);
                return;
            }

            // Clean up previous map instance to prevent target container collision and white screen bugs in Turbo Drive
            if (window.map) {
                try {
                    window.map.remove();
                } catch(e) {
                    console.error("Error removing Leaflet map instance:", e);
                }
                window.map = null;
            }

            const latInput = document.getElementById('office_latitude');
            const lngInput = document.getElementById('office_longitude');
            const radInput = document.getElementById('allowed_radius_meters');

            // Default to TVRI Yogyakarta or saved coordinates
            let lat = parseFloat(latInput.value) || -7.782875;
            let lng = parseFloat(lngInput.value) || 110.367035;
            let radius = parseInt(radInput.value) || 100;

            // Initialize Map
            window.map = L.map('map-container').setView([lat, lng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(window.map);

            // Initialize Marker
            window.marker = L.marker([lat, lng], { draggable: true }).addTo(window.map);

            // Initialize Radius Circle
            window.circle = L.circle([lat, lng], {
                color: '#ef4444',
                fillColor: '#ef4444',
                fillOpacity: 0.15,
                radius: radius
            }).addTo(window.map);

            // Marker Drag End Event
            window.marker.on('dragend', function (e) {
                let position = window.marker.getLatLng();
                latInput.value = position.lat.toFixed(6);
                lngInput.value = position.lng.toFixed(6);
                window.circle.setLatLng(position);
                window.map.panTo(position);
            });

            // Map Click Event
            window.map.on('click', function (e) {
                let position = e.latlng;
                window.marker.setLatLng(position);
                latInput.value = position.lat.toFixed(6);
                lngInput.value = position.lng.toFixed(6);
                window.circle.setLatLng(position);
            });

            // Sync inputs to Map circle radius
            radInput.addEventListener('input', function() {
                let r = parseInt(radInput.value) || 0;
                window.circle.setRadius(r);
            });

            // Sync coordinate inputs to Map marker
            function updateMapFromInputs() {
                let la = parseFloat(latInput.value);
                let lo = parseFloat(lngInput.value);
                if (!isNaN(la) && !isNaN(lo)) {
                    let pos = [la, lo];
                    window.marker.setLatLng(pos);
                    window.circle.setLatLng(pos);
                    window.map.setView(pos);
                }
            }

            latInput.addEventListener('change', updateMapFromInputs);
            lngInput.addEventListener('change', updateMapFromInputs);
        }
    </script>
    @endpush
</x-app-layout>
