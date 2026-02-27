<x-app-layout title="Absensi">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                <i class="fas fa-fingerprint text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
                <h1 class="page-title">Absensi Karyawan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>



    @if($errors->any())
        <div class="notification notification-danger mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Cuti Banner --}}
    @if($isOnLeave ?? false)
    <div class="card mb-6 border-l-4 border-indigo-500">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-4 flex-1">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/30">
                        <i class="fas fa-umbrella-beach text-2xl text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-indigo-700 dark:text-indigo-300">Hari Cuti</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $statusMessage }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>Anda tidak perlu melakukan absensi pada hari ini.
                        </p>
                    </div>
                </div>
                <div class="text-left sm:text-right">
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 font-semibold text-sm">
                        <i class="fas fa-calendar-check"></i>
                        Cuti
                    </span>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Status Card --}}
    <div class="card mb-6">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="stat-card-icon {{ ($canCheckIn || $canCheckOut) ? 'bg-blue-100 dark:bg-blue-900' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <i class="fas fa-clock text-xl {{ ($canCheckIn || $canCheckOut) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500' }}"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status Hari Ini</h3>
                        <p class="text-gray-500 dark:text-gray-400">{{ $statusMessage }}</p>
                    </div>
                </div>
                @if($todayAttendance)
                <div class="text-left sm:text-right">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Masuk: <span class="font-bold text-emerald-600">{{ $todayAttendance->check_in_time->format('H:i') }}</span>
                    </p>
                    @if($todayAttendance->min_check_out_time && !$todayAttendance->check_out_time)
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Min. Pulang: <span class="font-bold text-amber-600">{{ $todayAttendance->min_check_out_time->format('H:i') }}</span>
                        </p>
                    @endif
                    @if($todayAttendance->check_out_time)
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Pulang: <span class="font-bold text-emerald-600">{{ $todayAttendance->check_out_time->format('H:i') }}</span>
                        </p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Shift Info --}}
    @if(auth()->user()->isNormalAttendance() && $currentShift)
        <div class="card mb-6 border-l-4 border-emerald-500">
            <div class="card-body">
                <h4 class="font-bold text-emerald-700 dark:text-emerald-400 mb-3">
                    <i class="fas fa-calendar-alt mr-2"></i>Jadwal Kerja Normal
                </h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Jam Masuk</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($currentShift->start_time)->format('H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Jam Pulang</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($currentShift->end_time)->format('H:i') }}</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                    Toleransi: {{ $currentShift->tolerance_minutes }} menit | Durasi: 8 jam
                </p>
            </div>
        </div>
    @elseif(auth()->user()->isShiftAttendance())
        <div class="card mb-6 border-l-4 border-purple-500">
            <div class="card-body">
                <h4 class="font-bold text-purple-700 dark:text-purple-400 mb-3">
                    <i class="fas fa-shield-alt mr-2"></i>Jadwal Shift
                </h4>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($allShifts as $shift)
                        <div class="p-3 rounded-lg {{ $currentShift && $currentShift->id === $shift->id ? 'bg-purple-100 dark:bg-purple-900 border-2 border-purple-400' : 'bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600' }}">
                            <p class="font-bold {{ $currentShift && $currentShift->id === $shift->id ? 'text-purple-700 dark:text-purple-300' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $shift->name }}
                                @if($currentShift && $currentShift->id === $shift->id)
                                    <span class="badge badge-primary ml-1">AKTIF</span>
                                @endif
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                            </p>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">Toleransi: 30 menit | Durasi: 8 jam</p>
            </div>
        </div>
    @endif

    {{-- Countdown Timer --}}
    @if($todayAttendance && !$todayAttendance->check_out_time && $todayAttendance->min_check_out_time)
        <div class="card mb-6 border-l-4 border-amber-500">
            <div class="card-body">
                <h4 class="font-bold text-amber-700 dark:text-amber-400 mb-2">
                    <i class="fas fa-hourglass-half mr-2"></i>Waktu Tersisa
                </h4>
                <div id="countdown" class="text-3xl font-mono font-bold text-amber-800 dark:text-amber-300">
                    Menghitung...
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    Bisa pulang pada: <strong>{{ $todayAttendance->min_check_out_time->format('H:i') }}</strong>
                </p>
            </div>
        </div>
        
        <script>
            const minCheckOutTime = new Date('{{ $todayAttendance->min_check_out_time->toISOString() }}');
            
            function updateCountdown() {
                const now = new Date();
                const diff = minCheckOutTime - now;
                
                if (diff <= 0) {
                    document.getElementById('countdown').innerHTML = '<span class="text-emerald-600">✅ Sudah bisa pulang!</span>';
                    if (!{{ $canCheckOut ? 'true' : 'false' }}) {
                        location.reload();
                    }
                    return;
                }
                
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                
                document.getElementById('countdown').textContent = 
                    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
            
            updateCountdown();
            setInterval(updateCountdown, 1000);
        </script>
    @endif

    {{-- Attendance Form --}}
    @if($canCheckIn || $canCheckOut)
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Camera Section --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-camera mr-2 text-blue-500"></i>Ambil Foto Selfie</h3>
            </div>
            <div class="card-body">
                <div class="relative w-full h-64 bg-gray-200 dark:bg-slate-700 rounded-lg overflow-hidden flex items-center justify-center">
                    <video id="video" class="absolute w-full h-full object-cover hidden" autoplay playsinline></video>
                    <canvas id="canvas" class="hidden absolute w-full h-full object-cover"></canvas>
                    <div id="placeholder" class="text-gray-500 dark:text-gray-400 text-center">
                        <i class="fas fa-camera text-4xl mb-2"></i>
                        <p>Kamera Belum Aktif</p>
                    </div>
                </div>
                <div class="flex gap-2 mt-4 justify-center">
                    <button type="button" id="start-camera" class="btn btn-primary">
                        <i class="fas fa-video"></i> Aktifkan Kamera
                    </button>
                    <button type="button" id="snap" class="btn btn-success hidden">
                        <i class="fas fa-camera"></i> Ambil Foto
                    </button>
                    <button type="button" id="retake" class="btn btn-warning hidden">
                        <i class="fas fa-redo"></i> Foto Ulang
                    </button>
                </div>
            </div>
        </div>

        {{-- Location & Submit Section --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2 text-red-500"></i>Status Lokasi</h3>
            </div>
            <div class="card-body">
                <div id="location-status" class="p-4 bg-gray-50 dark:bg-slate-700 rounded-lg mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-spinner fa-spin mr-2 text-blue-500"></i>
                        <span>Mencari lokasi anda...</span>
                    </div>
                </div>
                
                @if($canCheckIn)
                <form action="{{ route('attendance.checkIn') }}" method="POST" enctype="multipart/form-data" id="attendance-form">
                    @csrf
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="accuracy" id="accuracy">
                    <input type="hidden" name="is_mock_location" id="is_mock_location" value="0">
                    <input type="file" name="photo" id="photo-input" class="hidden">
                    
                    <button type="submit" id="submit-btn" class="btn btn-lg btn-success w-full" disabled>
                        <i class="fas fa-sign-in-alt"></i> Absen Masuk
                    </button>
                </form>
                @endif

                @if($canCheckOut)
                <form action="{{ route('attendance.checkOut') }}" method="POST" enctype="multipart/form-data" id="attendance-form">
                    @csrf
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="accuracy" id="accuracy">
                    <input type="hidden" name="is_mock_location" id="is_mock_location" value="0">
                    <input type="file" name="photo" id="photo-input" class="hidden">
                    
                    <button type="submit" id="submit-btn" class="btn btn-lg btn-danger w-full" disabled>
                        <i class="fas fa-sign-out-alt"></i> Absen Pulang
                    </button>
                </form>
                @endif

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 text-center">
                    <i class="fas fa-info-circle mr-1"></i>
                    Aktifkan kamera dan pastikan lokasi terdeteksi untuk absen
                </p>
            </div>
        </div>
    </div>

    <script>
        // Camera Elements
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const startBtn = document.getElementById('start-camera');
        const snapBtn = document.getElementById('snap');
        const retakeBtn = document.getElementById('retake');
        const photoInput = document.getElementById('photo-input');
        const placeholder = document.getElementById('placeholder');

        // Location Elements
        const latInput = document.getElementById('latitude');
        const lonInput = document.getElementById('longitude');
        const locationStatus = document.getElementById('location-status');
        const submitBtn = document.getElementById('submit-btn');

        let isPhotoTaken = false;
        let isLocationValid = false;

        // Camera Logic
        startBtn.addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                video.srcObject = stream;
                video.classList.remove('hidden');
                placeholder.classList.add('hidden');
                startBtn.classList.add('hidden');
                snapBtn.classList.remove('hidden');
            } catch (err) {
                alert("Gagal mengakses kamera: " + err.message);
            }
        });

        snapBtn.addEventListener('click', () => {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
            
            canvas.classList.remove('hidden');
            video.classList.add('hidden');
            snapBtn.classList.add('hidden');
            retakeBtn.classList.remove('hidden');

            canvas.toBlob((blob) => {
                const file = new File([blob], "attendance_photo.jpg", { type: "image/jpeg" });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                photoInput.files = dataTransfer.files;
                
                isPhotoTaken = true;
                checkReady();
            }, 'image/jpeg');
        });

        retakeBtn.addEventListener('click', () => {
            canvas.classList.add('hidden');
            video.classList.remove('hidden');
            retakeBtn.classList.add('hidden');
            snapBtn.classList.remove('hidden');
            isPhotoTaken = false;
            photoInput.value = "";
            checkReady();
        });

        // Geolocation Logic with Fake GPS Detection
        let watchId = null;
        const gpsReadings = []; // Collect multiple readings for variance check
        const MIN_SAMPLES = 3;
        const accuracyInput = document.getElementById('accuracy');
        const mockInput = document.getElementById('is_mock_location');
        
        function startGeolocation() {
            gpsReadings.length = 0; // Reset readings
            locationStatus.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-spinner fa-spin mr-2 text-blue-500"></i>
                    <span>Mencari lokasi anda...</span>
                </div>
            `;
            
            if (navigator.geolocation) {
                watchId = navigator.geolocation.watchPosition(showPosition, showError, {
                    enableHighAccuracy: true,
                    timeout: 30000,
                    maximumAge: 0
                });
            } else {
                locationStatus.innerHTML = `<p class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Geolocation tidak didukung</p>`;
            }
        }
        
        startGeolocation();

        function detectMockGps(position) {
            const accuracy = position.coords.accuracy;
            
            // Check 1: Accuracy exactly 0 = definite mock
            if (accuracy === 0) return true;
            
            // Check 2: Accuracy too perfect (< 1m) = suspicious
            if (accuracy < 1) return true;
            
            // Check 3: Collect samples and check variance
            gpsReadings.push({
                lat: position.coords.latitude,
                lon: position.coords.longitude,
                accuracy: accuracy,
                timestamp: position.timestamp
            });
            
            if (gpsReadings.length >= MIN_SAMPLES) {
                // Check if all readings are exactly identical (fake GPS doesn't vary)
                const lats = gpsReadings.map(r => r.lat);
                const lons = gpsReadings.map(r => r.lon);
                const latVariance = Math.max(...lats) - Math.min(...lats);
                const lonVariance = Math.max(...lons) - Math.min(...lons);
                
                // Real GPS always has micro-variations (> 0.000001 degree ≈ 0.1m)
                // Fake GPS returns exactly the same value every time
                if (latVariance === 0 && lonVariance === 0) return true;
            }
            
            return false;
        }

        function showPosition(position) {
            latInput.value = position.coords.latitude;
            lonInput.value = position.coords.longitude;
            accuracyInput.value = position.coords.accuracy;
            
            // Run mock GPS detection
            const isMock = detectMockGps(position);
            mockInput.value = isMock ? '1' : '0';
            
            const accuracyText = Math.round(position.coords.accuracy);
            let mockWarning = '';
            
            if (isMock) {
                mockWarning = `
                    <div class="mt-2 p-2 bg-red-100 dark:bg-red-900/30 rounded text-red-700 dark:text-red-400 text-xs">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Terdeteksi indikasi lokasi palsu (fake GPS). Absensi akan ditolak.
                    </div>
                `;
                // Block submission
                isLocationValid = false;
            } else {
                isLocationValid = true;
            }
            
            locationStatus.innerHTML = `
                <div class="text-emerald-600 dark:text-emerald-400 font-semibold mb-2">
                    <i class="fas fa-check-circle mr-1"></i>Lokasi Ditemukan!
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Lat: ${position.coords.latitude.toFixed(6)}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Long: ${position.coords.longitude.toFixed(6)}</p>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Akurasi: ${accuracyText} meter | Sampel: ${gpsReadings.length}/${MIN_SAMPLES}</p>
                ${mockWarning}
            `;
            
            checkReady();
        }

        function showError(error) {
            let msg = "";
            let tip = "";
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    msg = "Akses lokasi ditolak.";
                    tip = "Aktifkan izin lokasi di browser.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    msg = "Lokasi tidak tersedia.";
                    tip = "Aktifkan GPS di perangkat.";
                    break;
                case error.TIMEOUT:
                    msg = "Request timeout.";
                    tip = "Koneksi lambat atau GPS belum aktif.";
                    break;
                default:
                    msg = "Terjadi kesalahan.";
                    tip = "Coba refresh halaman.";
            }
            locationStatus.innerHTML = `
                <div class="text-red-600 dark:text-red-400 font-semibold mb-2">
                    <i class="fas fa-times-circle mr-1"></i>${msg}
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">${tip}</p>
                <button type="button" onclick="retryLocation()" class="btn btn-sm btn-primary">
                    <i class="fas fa-redo"></i> Coba Lagi
                </button>
            `;
            isLocationValid = false;
            checkReady();
        }
        
        window.retryLocation = function() {
            if (watchId) navigator.geolocation.clearWatch(watchId);
            startGeolocation();
        };

        function checkReady() {
            if (isPhotoTaken && isLocationValid) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
    </script>
    @endif

</x-app-layout>
