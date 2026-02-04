<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Absensi Karyawan') }}
            @if(auth()->user()->employee_type)
                <span class="text-sm font-normal text-gray-500 ml-2">
                    ({{ auth()->user()->getEmployeeTypeLabel() }})
                </span>
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Sukses!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Gagal!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Status Card -->
                    <div class="mb-6 p-4 rounded-lg {{ ($canCheckIn || $canCheckOut) ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50 border border-gray-200' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">Status Hari Ini</h3>
                                <p class="text-gray-600">{{ $statusMessage }}</p>
                            </div>
                            <div class="text-right">
                                @if($todayAttendance)
                                    <p class="text-sm text-gray-500">Absen Masuk: <span class="font-bold text-green-600">{{ $todayAttendance->check_in_time->format('H:i') }}</span></p>
                                    @if($todayAttendance->min_check_out_time && !$todayAttendance->check_out_time)
                                        <p class="text-sm text-gray-500">Minimal Pulang: <span class="font-bold text-orange-600">{{ $todayAttendance->min_check_out_time->format('H:i') }}</span></p>
                                    @endif
                                    @if($todayAttendance->check_out_time)
                                        <p class="text-sm text-gray-500">Absen Pulang: <span class="font-bold text-green-600">{{ $todayAttendance->check_out_time->format('H:i') }}</span></p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Shift/Schedule Info -->
                    @if(auth()->user()->isOB() && $currentShift)
                        <!-- OB Normal Schedule -->
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h4 class="font-bold text-green-700 mb-2">📋 Jadwal Kerja Normal (OB)</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600">Jam Masuk:</p>
                                    <p class="font-bold text-lg">{{ \Carbon\Carbon::parse($currentShift->start_time)->format('H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Jam Pulang:</p>
                                    <p class="font-bold text-lg">{{ \Carbon\Carbon::parse($currentShift->end_time)->format('H:i') }}</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Toleransi keterlambatan: {{ $currentShift->tolerance_minutes }} menit | Durasi kerja: 8 jam</p>
                        </div>
                    @elseif(auth()->user()->isSatpam())
                        <!-- Satpam Shift Schedule -->
                        <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                            <h4 class="font-bold text-purple-700 mb-3">🛡️ Jadwal Shift Satpam</h4>
                            <div class="grid grid-cols-3 gap-3 text-sm">
                                @foreach($allShifts as $shift)
                                    <div class="p-3 rounded-lg {{ $currentShift && $currentShift->id === $shift->id ? 'bg-purple-200 border-2 border-purple-400' : 'bg-white border border-gray-200' }}">
                                        <p class="font-bold {{ $currentShift && $currentShift->id === $shift->id ? 'text-purple-700' : 'text-gray-700' }}">
                                            {{ $shift->name }}
                                            @if($currentShift && $currentShift->id === $shift->id)
                                                <span class="text-xs bg-purple-500 text-white px-2 py-0.5 rounded ml-1">AKTIF</span>
                                            @endif
                                        </p>
                                        <p class="text-gray-600">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-3">Toleransi keterlambatan: 30 menit | Durasi kerja per shift: 8 jam</p>
                        </div>
                    @endif

                    <!-- Countdown Timer for Check-out -->
                    @if($todayAttendance && !$todayAttendance->check_out_time && $todayAttendance->min_check_out_time)
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="font-bold text-yellow-700 mb-2">⏱️ Waktu Tersisa</h4>
                            <div id="countdown" class="text-2xl font-mono font-bold text-yellow-800">
                                Menghitung...
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Anda bisa absen pulang pada: <strong>{{ $todayAttendance->min_check_out_time->format('H:i') }}</strong></p>
                        </div>
                        
                        <script>
                            const minCheckOutTime = new Date('{{ $todayAttendance->min_check_out_time->toISOString() }}');
                            
                            function updateCountdown() {
                                const now = new Date();
                                const diff = minCheckOutTime - now;
                                
                                if (diff <= 0) {
                                    document.getElementById('countdown').innerHTML = '<span class="text-green-600">✅ Anda sudah bisa absen pulang!</span>';
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

                    @if($canCheckIn || $canCheckOut)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Camera Section -->
                        <div class="flex flex-col items-center">
                            <h3 class="text-lg font-medium mb-2">Ambil Foto Selfie</h3>
                            <div class="relative w-full max-w-sm h-64 bg-gray-200 rounded-lg overflow-hidden flex items-center justify-center">
                                <video id="video" class="absolute w-full h-full object-cover" autoplay playsinline></video>
                                <canvas id="canvas" class="hidden absolute w-full h-full object-cover"></canvas>
                                <div id="placeholder" class="text-gray-500">Kamera Belum Aktif</div>
                            </div>
                            <button type="button" id="start-camera" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Aktifkan Kamera</button>
                            <button type="button" id="snap" class="mt-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 hidden">Ambil Foto</button>
                            <button type="button" id="retake" class="mt-2 px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 hidden">Foto Ulang</button>
                        </div>

                        <!-- Location & Submit Section -->
                        <div class="flex flex-col justify-center">
                            <h3 class="text-lg font-medium mb-2">Status Lokasi</h3>
                            <div id="location-status" class="p-4 bg-gray-50 rounded mb-4">
                                Mencari lokasi anda...
                            </div>
                            
                            @if($canCheckIn)
                            <form action="{{ route('attendance.checkIn') }}" method="POST" enctype="multipart/form-data" id="attendance-form">
                                @csrf
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">
                                <input type="file" name="photo" id="photo-input" class="hidden">
                                
                                <button type="submit" id="submit-btn" class="w-full px-4 py-3 bg-gray-400 text-white rounded font-bold cursor-not-allowed" disabled>
                                    🟢 Absen Masuk
                                </button>
                            </form>
                            @endif

                            @if($canCheckOut)
                            <form action="{{ route('attendance.checkOut') }}" method="POST" enctype="multipart/form-data" id="attendance-form">
                                @csrf
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">
                                <input type="file" name="photo" id="photo-input" class="hidden">
                                
                                <button type="submit" id="submit-btn" class="w-full px-4 py-3 bg-gray-400 text-white rounded font-bold cursor-not-allowed" disabled>
                                    🔴 Absen Pulang
                                </button>
                            </form>
                            @endif
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

                        // 1. Camera Logic
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

                        // 2. Geolocation Logic
                        let watchId = null;
                        
                        function startGeolocation() {
                            locationStatus.innerHTML = `
                                <div class="flex items-center">
                                    <svg class="animate-spin h-5 w-5 mr-2 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Mencari lokasi anda...</span>
                                </div>
                            `;
                            
                            if (navigator.geolocation) {
                                watchId = navigator.geolocation.watchPosition(showPosition, showError, {
                                    enableHighAccuracy: true,
                                    timeout: 30000, // 30 detik timeout
                                    maximumAge: 60000 // Cache lokasi selama 1 menit
                                });
                            } else {
                                locationStatus.innerHTML = "Geolocation tidak didukung browser ini.";
                            }
                        }
                        
                        // Start geolocation on page load
                        startGeolocation();

                        function showPosition(position) {
                            latInput.value = position.coords.latitude;
                            lonInput.value = position.coords.longitude;
                            
                            locationStatus.innerHTML = `
                                <p class="text-green-600 font-bold">✅ Lokasi Ditemukan!</p>
                                <p>Lat: ${position.coords.latitude.toFixed(6)}</p>
                                <p>Long: ${position.coords.longitude.toFixed(6)}</p>
                                <p class="text-xs text-gray-500">Akurasi: ${Math.round(position.coords.accuracy)} meter</p>
                            `;
                            
                            isLocationValid = true;
                            checkReady();
                        }

                        function showError(error) {
                            let msg = "";
                            let tip = "";
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    msg = "Akses lokasi ditolak.";
                                    tip = "Pastikan izin lokasi diaktifkan di browser.";
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    msg = "Info lokasi tidak tersedia.";
                                    tip = "Pastikan GPS/lokasi aktif di perangkat.";
                                    break;
                                case error.TIMEOUT:
                                    msg = "Request timeout.";
                                    tip = "Koneksi lambat atau GPS belum aktif.";
                                    break;
                                case error.UNKNOWN_ERROR:
                                    msg = "Terjadi kesalahan.";
                                    tip = "Coba refresh halaman.";
                                    break;
                            }
                            locationStatus.innerHTML = `
                                <p class="text-red-600 font-bold">❌ Error: ${msg}</p>
                                <p class="text-sm text-gray-500 mb-2">${tip}</p>
                                <button type="button" onclick="retryLocation()" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                                    🔄 Coba Lagi
                                </button>
                            `;
                            isLocationValid = false;
                            checkReady();
                        }
                        
                        // Global function for retry button
                        window.retryLocation = function() {
                            if (watchId) {
                                navigator.geolocation.clearWatch(watchId);
                            }
                            startGeolocation();
                        };

                        function checkReady() {
                            if (isPhotoTaken && isLocationValid) {
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                                submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                            } else {
                                submitBtn.disabled = true;
                                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                                submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                            }
                        }
                    </script>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
