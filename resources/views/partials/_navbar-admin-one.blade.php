{{-- Admin One Style Navbar --}}
<nav 
    class="navbar lg:pl-60"
>
    <div class="navbar-start">
        {{-- Mobile Menu Toggle --}}
        <div 
            @click.prevent="isAsideMobileExpanded = !isAsideMobileExpanded"
            class="navbar-item lg:hidden cursor-pointer"
        >
            <span class="icon icon-lg">
                <i class="fas" :class="isAsideMobileExpanded ? 'fa-arrow-left' : 'fa-bars'"></i>
            </span>
        </div>

        {{-- Search --}}
        <div class="navbar-search">
            <div class="relative w-full max-w-xs">
                <span class="form-control-icon">
                    <i class="fas fa-search"></i>
                </span>
                <input 
                    type="text" 
                    placeholder="Cari..." 
                    class="form-control form-control-sm form-control-with-icon !pl-10 bg-transparent border-0 focus:bg-white dark:focus:bg-slate-700"
                >
            </div>
        </div>
    </div>

    <div class="navbar-end">
        {{-- Dark Mode Toggle --}}
        <div @click="toggleDarkMode()" class="navbar-item cursor-pointer">
            <span class="icon icon-lg">
                <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
            </span>
        </div>

        {{-- Notifications --}}
        <div x-data="{ open: false }" class="dropdown">
            <div @click="open = !open" class="navbar-item cursor-pointer">
                <span class="icon icon-lg relative">
                    <i class="fas fa-bell"></i>
                    @if(isset($notifications) && $notifications->count() > 0)
                        <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] text-white">{{ $notifications->count() }}</span>
                    @endif
                </span>
            </div>
            <div x-show="open" @click.outside="open = false" x-transition class="dropdown-menu !w-80 !max-h-96 !overflow-y-auto" x-cloak>
                <div class="px-4 py-2.5 flex items-center justify-between border-b dark:border-slate-700">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Notifikasi</span>
                    @if(isset($notifications) && $notifications->count() > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-medium hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                Tandai semua dibaca
                            </button>
                        </form>
                    @endif
                </div>
                @if(isset($notifications) && $notifications->count() > 0)
                    @foreach($notifications as $notif)
                        <a href="{{ route('notifications.read', $notif->id) }}" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors border-b border-gray-50 dark:border-slate-700/50 last:border-0">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-700 mt-0.5">
                                <i class="fas {{ $notif->icon }} text-xs {{ $notif->color }}"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white leading-tight">{{ $notif->message }}</p>
                                @if($notif->detail)
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $notif->detail }}</p>
                                @endif
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="px-4 py-8 text-center">
                        <i class="fas fa-bell-slash text-2xl text-gray-300 dark:text-gray-600 mb-2"></i>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada notifikasi</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- User Menu --}}
        @auth
        <div x-data="{ open: false }" class="dropdown">
            <div @click="open = !open" class="navbar-item cursor-pointer gap-2">
                <div class="avatar avatar-sm bg-blue-600 text-white font-semibold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="hidden text-sm font-medium lg:inline">{{ auth()->user()->name }}</span>
                <i class="fas fa-chevron-down text-xs"></i>
            </div>
            <div x-show="open" @click.outside="open = false" x-transition class="dropdown-menu" x-cloak>
                <div class="px-4 py-2">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">{{ auth()->user()->email }}</div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                    <i class="fas fa-user mr-2"></i> Profil
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}"
                      data-confirm="Apakah Anda yakin ingin keluar dari sistem?"
                      data-confirm-title="Konfirmasi Logout">
                    @csrf
                    <button type="submit" class="dropdown-item w-full text-left text-red-600 dark:text-red-400">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>
