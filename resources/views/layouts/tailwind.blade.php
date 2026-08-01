<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ session('theme', 'light') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'TVRI Presensi' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo tvri.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Turbo Drive for instant page transitions -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.12/dist/turbo.es2017-esm.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Turbo Progress Bar */
        .turbo-progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
            z-index: 99999;
            transition: width 0.3s ease;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="font-inter antialiased">
    <div x-data="{ 
        isAsideMobileExpanded: false, 
        isAsideLgActive: true,
        darkMode: document.documentElement.classList.contains('dark'),
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            document.documentElement.classList.toggle('dark', this.darkMode);
            fetch('{{ route('theme.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ theme: this.darkMode ? 'dark' : 'light' })
            });
        }
    }" class="relative">
        
        {{-- Aside Menu --}}
        @include('partials._sidebar')
        
        {{-- Main Content Area --}}
        <div 
            class="min-h-screen w-screen pt-14 transition-all duration-300 lg:pl-60 bg-gray-50 dark:bg-slate-800 dark:text-slate-100 lg:w-auto"
        >
            {{-- Navbar --}}
            @include('partials._navbar-admin-one')
            
            {{-- Main Section --}}
            <main class="section-main">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="notification notification-success mb-6">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-xl"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('recovery_codes'))
                    <div class="mb-6 p-5 bg-amber-50 dark:bg-amber-950/20 border-l-4 border-amber-500 rounded-r-lg shadow-sm text-amber-900 dark:text-amber-200">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-exclamation-triangle text-xl text-amber-500"></i>
                                    <span class="font-bold text-lg">PENTING: Simpan Recovery Code ini di tempat aman!</span>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="copyRecoveryCodes()" class="bg-white hover:bg-gray-100 text-gray-800 border border-gray-300 dark:bg-slate-900 dark:hover:bg-slate-800 dark:text-white dark:border-slate-700 py-1.5 px-3 rounded text-xs flex items-center gap-1.5 font-semibold transition-colors shadow-sm">
                                        <i class="fas fa-copy"></i>
                                        <span>Salin Semua</span>
                                    </button>
                                    <button onclick="downloadRecoveryCodes()" class="bg-amber-600 hover:bg-amber-700 text-white py-1.5 px-3 rounded text-xs flex items-center gap-1.5 font-semibold transition-colors shadow-sm">
                                        <i class="fas fa-download"></i>
                                        <span>Unduh (.txt)</span>
                                    </button>
                                </div>
                            </div>
                            <p class="text-sm">Kode pemulihan ini **hanya ditampilkan sekali saja**. Gunakan salah satu kode di bawah ini untuk masuk ke akun jika Anda kehilangan akses Google Authenticator:</p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-2 font-mono text-center font-bold tracking-wider text-gray-900 dark:text-white select-all">
                                @foreach(session('recovery_codes') as $code)
                                    <div class="p-3 bg-white dark:bg-slate-900 rounded-lg shadow-xs border border-amber-200 dark:border-slate-800 text-sm hover:bg-amber-100 dark:hover:bg-slate-800 transition-colors recovery-code-item">
                                        {{ $code }}
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-2 text-xs text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                                <i class="fas fa-info-circle"></i>
                                <span>Masing-masing kode di atas bersifat sekali pakai (*single-use*).</span>
                            </div>
                        </div>
                    </div>

                    <script>
                        function getRecoveryCodesText() {
                            const codes = [];
                            document.querySelectorAll('.recovery-code-item').forEach((el) => {
                                codes.push(el.innerText.trim());
                            });
                            return codes;
                        }

                        function copyRecoveryCodes() {
                            const codes = getRecoveryCodesText().join('\n');
                            navigator.clipboard.writeText(codes).then(() => {
                                alert('Semua kode pemulihan berhasil disalin ke clipboard!');
                            }).catch(err => {
                                console.error('Gagal menyalin kode: ', err);
                            });
                        }

                        function downloadRecoveryCodes() {
                            const codes = getRecoveryCodesText();
                            let content = "=== KODE PEMULIHAN 2FA - TVRI ABSENSI ===\n";
                            content += "Tanggal Dibuat: " + new Date().toLocaleDateString('id-ID') + "\n";
                            content += "Simpan file ini di tempat yang aman. Jangan bagikan kepada siapa pun.\n";
                            content += "Setiap kode di bawah ini hanya dapat digunakan SATU KALI untuk masuk.\n\n";
                            
                            codes.forEach((code, index) => {
                                content += `${index + 1}. ${code}\n`;
                            });

                            const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
                            const url = URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = 'tvri-absensi-recovery-codes.txt';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            URL.revokeObjectURL(url);
                        }
                    </script>
                @endif

                @if(session('error'))
                    <div class="notification notification-danger mb-6">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-exclamation-circle text-xl"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                {{-- Header Slot --}}
                @if(isset($header))
                    <div class="section-title-bar">
                        <div class="section-title-bar-main">
                            {{ $header }}
                        </div>
                    </div>
                @endif

                {{-- Content --}}
                {{ $slot }}
            </main>

            {{-- Footer --}}
            <footer class="p-6 text-center text-sm text-gray-500 dark:text-slate-400">
                <p>&copy; {{ date('Y') }} TVRI. All rights reserved.</p>
            </footer>
        </div>

        {{-- Mobile Overlay --}}
        <div 
            x-show="isAsideMobileExpanded" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="isAsideMobileExpanded = false"
            class="fixed inset-0 z-30 bg-black/50 lg:hidden"
            x-cloak
        ></div>
    </div>

    {{-- Global Confirmation Dialog --}}
    <div id="confirmModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
        <div class="mx-4 w-full max-w-sm rounded-xl bg-white p-6 shadow-2xl dark:bg-slate-800">
            <div class="mb-4 flex justify-center">
                <div id="confirmIcon" class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-600 dark:text-red-400"></i>
                </div>
            </div>
            <h3 id="confirmTitle" class="mb-2 text-center text-lg font-bold text-gray-900 dark:text-white">Konfirmasi</h3>
            <p id="confirmMessage" class="mb-6 text-center text-sm text-gray-600 dark:text-gray-400">Apakah Anda yakin?</p>
            <div class="flex gap-3">
                <button id="confirmCancel" class="btn btn-secondary flex-1">Batal</button>
                <button id="confirmOk" class="btn btn-danger flex-1">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const modal = document.getElementById('confirmModal');
            const titleEl = document.getElementById('confirmTitle');
            const msgEl = document.getElementById('confirmMessage');
            const okBtn = document.getElementById('confirmOk');
            const cancelBtn = document.getElementById('confirmCancel');
            let pendingForm = null;

            function showModal(title, message) {
                titleEl.textContent = title;
                msgEl.textContent = message;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function hideModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                pendingForm = null;
            }

            // Intercept all forms with data-confirm
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.hasAttribute('data-confirm') && !form.dataset.confirmed) {
                    e.preventDefault();
                    pendingForm = form;
                    const title = form.dataset.confirmTitle || 'Konfirmasi';
                    const message = form.dataset.confirm;
                    showModal(title, message);
                }
            });

            okBtn.addEventListener('click', function() {
                if (pendingForm) {
                    pendingForm.dataset.confirmed = 'true';
                    pendingForm.submit();
                }
                hideModal();
            });

            cancelBtn.addEventListener('click', hideModal);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) hideModal();
            });
        })();
    </script>

    <script>
        // Turbo Progress Bar
        (function() {
            let progressBar = null;
            let progressValue = 0;
            let progressInterval = null;

            function createBar() {
                progressBar = document.createElement('div');
                progressBar.className = 'turbo-progress-bar';
                progressBar.style.width = '0%';
                document.body.prepend(progressBar);
            }

            function startProgress() {
                if (progressBar) progressBar.remove();
                createBar();
                progressValue = 0;
                progressInterval = setInterval(function() {
                    progressValue += Math.random() * 15;
                    if (progressValue > 90) progressValue = 90;
                    progressBar.style.width = progressValue + '%';
                }, 100);
            }

            function completeProgress() {
                clearInterval(progressInterval);
                if (progressBar) {
                    progressBar.style.width = '100%';
                    setTimeout(function() {
                        if (progressBar) {
                            progressBar.style.opacity = '0';
                            setTimeout(function() { if (progressBar) progressBar.remove(); }, 300);
                        }
                    }, 200);
                }
            }

            document.addEventListener('turbo:before-fetch-request', startProgress);
            document.addEventListener('turbo:load', completeProgress);
            document.addEventListener('turbo:before-fetch-response', completeProgress);

            // Disable Turbo on forms (use traditional submit for CSRF/confirmation compat)
            document.addEventListener('turbo:before-fetch-request', function(e) {
                if (e.target && e.target.tagName === 'FORM') {
                    e.target.setAttribute('data-turbo', 'false');
                }
            });
        })();
    </script>

    @stack('scripts')

    {{-- Auto-logout: redirect to login when session expires --}}
    @auth
    <script>
    (function() {
        const SESSION_LIFETIME = {{ config('session.lifetime') }};
        // Auto-redirect after session lifetime
        setTimeout(function() {
            window.location.href = '{{ route("login") }}';
        }, SESSION_LIFETIME * 60 * 1000);
    })();
    </script>
    @endauth
</body>
</html>
