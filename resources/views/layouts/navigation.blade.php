<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ auth()->user()->role === 'user' ? route('user.home') : route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    
                    @if(Auth::user()->role === 'user')
                        {{-- User Menu --}}
                        <x-nav-link :href="route('user.home')" :active="request()->routeIs('user.home')">
                            {{ __('Beranda') }}
                        </x-nav-link>
                        <x-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.index')">
                            {{ __('Absensi') }}
                        </x-nav-link>
                        <x-nav-link :href="route('user.rekap')" :active="request()->routeIs('user.rekap')">
                            {{ __('Rekap') }}
                        </x-nav-link>
                        <x-nav-link :href="route('user.salary')" :active="request()->routeIs('user.salary')">
                            {{ __('Slip Gaji') }}
                        </x-nav-link>
                        <x-nav-link :href="route('user.leaves')" :active="request()->routeIs('user.leaves*')">
                            {{ __('Cuti') }}
                        </x-nav-link>
                        <x-nav-link :href="route('user.business-trips')" :active="request()->routeIs('user.business-trips*')">
                            {{ __('Dinas Luar') }}
                        </x-nav-link>
                    
                    @elseif(Auth::user()->role === 'staff_psdm')
                        {{-- Staff PSDM Menu --}}
                        <x-nav-link :href="route('staff.psdm.dashboard')" :active="request()->routeIs('staff.psdm.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('staff.psdm.users')" :active="request()->routeIs('staff.psdm.users*')">
                            {{ __('Kelola User') }}
                        </x-nav-link>
                        <x-nav-link :href="route('staff.psdm.announcements')" :active="request()->routeIs('staff.psdm.announcements*')">
                            {{ __('Pengumuman') }}
                        </x-nav-link>
                        <x-nav-link :href="route('staff.psdm.monitor')" :active="request()->routeIs('staff.psdm.monitor')">
                            {{ __('Monitor') }}
                        </x-nav-link>
                    
                    @elseif(Auth::user()->role === 'staff_keuangan')
                        {{-- Staff Keuangan Menu --}}
                        <x-nav-link :href="route('staff.keuangan.dashboard')" :active="request()->routeIs('staff.keuangan.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('staff.keuangan.users')" :active="request()->routeIs('staff.keuangan.users*') || request()->routeIs('staff.keuangan.salaries.input*') || request()->routeIs('staff.keuangan.salaries.import*')">
                            {{ __('Input Gaji') }}
                        </x-nav-link>
                        <x-nav-link :href="route('staff.keuangan.salaries')" :active="request()->routeIs('staff.keuangan.salaries') && !request()->routeIs('staff.keuangan.salaries.input*') && !request()->routeIs('staff.keuangan.salaries.import*')">
                            {{ __('Data Gaji') }}
                        </x-nav-link>
                        <x-nav-link :href="route('staff.keuangan.deductions.index')" :active="request()->routeIs('staff.keuangan.deductions*')">
                            {{ __('Jenis Potongan') }}
                        </x-nav-link>
                    
                    @elseif(Auth::user()->role === 'admin')
                        {{-- Admin Menu --}}
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.staffs')" :active="request()->routeIs('admin.staffs*')">
                            {{ __('Kelola Staff') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.admins')" :active="request()->routeIs('admin.admins*')">
                            {{ __('Kelola Admin') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.monitor')" :active="request()->routeIs('admin.monitor')">
                            {{ __('Monitor') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.leaves')" :active="request()->routeIs('admin.leaves')">
                            {{ __('Cuti') }}
                        </x-nav-link>
                    @endif

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <img class="h-8 w-8 rounded-full object-cover mr-2" src="{{ Auth::user()->getProfilePhotoUrl() }}" alt="{{ Auth::user()->name }}">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 text-xs text-gray-400 border-b">
                            {{ ucwords(str_replace('_', ' ', Auth::user()->role)) }}
                        </div>
                        
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            
            @if(Auth::user()->role === 'user')
                <x-responsive-nav-link :href="route('user.home')" :active="request()->routeIs('user.home')">
                    {{ __('Beranda') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.index')">
                    {{ __('Absensi') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('user.rekap')" :active="request()->routeIs('user.rekap')">
                    {{ __('Rekap') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('user.salary')" :active="request()->routeIs('user.salary')">
                    {{ __('Slip Gaji') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('user.leaves')" :active="request()->routeIs('user.leaves*')">
                    {{ __('Cuti') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('user.business-trips')" :active="request()->routeIs('user.business-trips*')">
                    {{ __('Dinas Luar') }}
                </x-responsive-nav-link>
            
            @elseif(Auth::user()->role === 'staff_psdm')
                <x-responsive-nav-link :href="route('staff.psdm.dashboard')" :active="request()->routeIs('staff.psdm.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.psdm.users')" :active="request()->routeIs('staff.psdm.users*')">
                    {{ __('Kelola User') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.psdm.announcements')" :active="request()->routeIs('staff.psdm.announcements*')">
                    {{ __('Pengumuman') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.psdm.monitor')" :active="request()->routeIs('staff.psdm.monitor')">
                    {{ __('Monitor') }}
                </x-responsive-nav-link>
            
            @elseif(Auth::user()->role === 'staff_keuangan')
                <x-responsive-nav-link :href="route('staff.keuangan.dashboard')" :active="request()->routeIs('staff.keuangan.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.keuangan.users')" :active="request()->routeIs('staff.keuangan.users*') || request()->routeIs('staff.keuangan.salaries.input*')">
                    {{ __('Input Gaji') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.keuangan.salaries')" :active="request()->routeIs('staff.keuangan.salaries')">
                    {{ __('Data Gaji') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.keuangan.deductions.index')" :active="request()->routeIs('staff.keuangan.deductions*')">
                    {{ __('Jenis Potongan') }}
                </x-responsive-nav-link>
            
            @elseif(Auth::user()->role === 'admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.staffs')" :active="request()->routeIs('admin.staffs*')">
                    {{ __('Kelola Staff') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.admins')" :active="request()->routeIs('admin.admins*')">
                    {{ __('Kelola Admin') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.monitor')" :active="request()->routeIs('admin.monitor')">
                    {{ __('Monitor') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.leaves')" :active="request()->routeIs('admin.leaves')">
                    {{ __('Cuti') }}
                </x-responsive-nav-link>
            @endif

        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

