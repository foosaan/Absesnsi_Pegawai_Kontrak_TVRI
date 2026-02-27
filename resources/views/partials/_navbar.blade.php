{{-- Navbar --}}
<nav class="navbar dark:navbar-dark">
    {{-- Left Side --}}
    <div class="flex items-center gap-4">
        {{-- Mobile Menu Toggle --}}
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
            <i class="fas fa-bars text-lg"></i>
        </button>
        
        {{-- Search (Desktop) --}}
        <div class="hidden md:block relative">
            <i class="fas fa-search input-icon"></i>
            <input type="text" 
                   placeholder="Cari... (Ctrl+K)" 
                   class="form-control form-control-with-icon w-64 py-1.5 text-sm">
        </div>
    </div>
    
    {{-- Right Side --}}
    <div class="flex items-center gap-3">
        {{-- Dark Mode Toggle --}}
        <button onclick="toggleDarkMode()" class="btn-icon text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <i class="fas fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:inline"></i>
        </button>
        
        {{-- Notifications --}}
        <button class="btn-icon text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 relative">
            <i class="fas fa-bell"></i>
            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] text-white">3</span>
        </button>
        
        {{-- User Dropdown --}}
        @auth
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-slate-700">
                <div class="avatar avatar-sm bg-blue-600 text-white">
                    @if(auth()->user()->foto)
                        <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="" class="h-full w-full rounded-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
                <span class="hidden md:inline text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ auth()->user()->name }}
                </span>
                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
            </button>
            
            {{-- Dropdown Menu --}}
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="dropdown-menu dark:bg-slate-800 dark:border-slate-700"
                 style="display: none;">
                <div class="px-4 py-2 border-b dark:border-slate-700">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="dropdown-item dark:text-gray-300 dark:hover:bg-slate-700">
                    <i class="fas fa-user w-4 mr-2"></i> Profil
                </a>
                <form method="POST" action="{{ route('logout') }}"
                      data-confirm="Apakah Anda yakin ingin keluar dari sistem?"
                      data-confirm-title="Konfirmasi Logout">
                    @csrf
                    <button type="submit" class="dropdown-item w-full text-left text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <i class="fas fa-sign-out-alt w-4 mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>

@push('scripts')
<script>
    function toggleDarkMode() {
        document.documentElement.classList.toggle('dark');
        // Save preference
        fetch('{{ route("theme.toggle") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light' 
            })
        }).catch(() => {});
    }
</script>
@endpush
