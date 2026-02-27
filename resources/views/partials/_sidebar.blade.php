<aside 
    :class="{
        '-left-60 lg:left-0 xl:left-0': !isAsideMobileExpanded,
        'left-0': isAsideMobileExpanded
    }"
    class="aside"
>
    {{-- Brand --}}
    <div class="aside-brand">
        @php
            $brandRoute = 'dashboard';
            $r = auth()->user()->role ?? 'user';
            if($r === 'admin') $brandRoute = 'admin.dashboard';
            elseif($r === 'staff_psdm') $brandRoute = 'staff.psdm.dashboard';
            elseif($r === 'staff_keuangan') $brandRoute = 'staff.keuangan.dashboard';
        @endphp
        <a href="{{ route($brandRoute) }}" class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600">
                <i class="fas fa-tv text-lg text-white"></i>
            </div>
            <span class="aside-brand-text">TVRI Absensi</span>
        </a>
    </div>

    {{-- Menu --}}
    <div class="aside-scrollbar">
        @auth
            @php
                $role = auth()->user()->role ?? 'user';
            @endphp
            
            <ul class="aside-menu-list">
                {{-- Dashboard --}}
                <li class="aside-menu-label">General</li>
                <li>
                    @php
                        $dashboardRoute = 'dashboard';
                        if($role === 'admin') $dashboardRoute = 'admin.dashboard';
                        elseif($role === 'staff_psdm') $dashboardRoute = 'staff.psdm.dashboard';
                        elseif($role === 'staff_keuangan') $dashboardRoute = 'staff.keuangan.dashboard';
                    @endphp
                    
                    <a href="{{ route($dashboardRoute) }}" 
                       class="aside-menu-item {{ request()->routeIs($dashboardRoute) ? 'active' : '' }}">
                        <span class="icon"><i class="fas fa-desktop"></i></span>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                {{-- Role Specific Menus --}}
                @if($role === 'admin')
                    <li class="aside-menu-label">Manajemen</li>
                    <li>
                        <a href="{{ route('admin.staffs') }}" class="aside-menu-item {{ request()->routeIs('admin.staffs*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-user-tie"></i></span>
                            <span class="menu-text">Staff</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.admins') }}" class="aside-menu-item {{ request()->routeIs('admin.admins*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-user-shield"></i></span>
                            <span class="menu-text">Admin</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.monitor') }}" class="aside-menu-item {{ request()->routeIs('admin.monitor') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-calendar-check"></i></span>
                            <span class="menu-text">Monitor Absensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.leaves') }}" class="aside-menu-item {{ request()->routeIs('admin.leaves') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-calendar-minus"></i></span>
                            <span class="menu-text">Cuti</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.activity-logs') }}" class="aside-menu-item {{ request()->routeIs('admin.activity-logs') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-history"></i></span>
                            <span class="menu-text">Log Aktivitas</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings') }}" class="aside-menu-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-cog"></i></span>
                            <span class="menu-text">Pengaturan</span>
                        </a>
                    </li>

                @elseif($role === 'staff_psdm')
                    <li class="aside-menu-label">PSDM</li>
                    <li>
                        <a href="{{ route('staff.psdm.users') }}" class="aside-menu-item {{ request()->routeIs('staff.psdm.users*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-users"></i></span>
                            <span class="menu-text">Karyawan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.psdm.monitor') }}" class="aside-menu-item {{ request()->routeIs('staff.psdm.monitor*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-calendar-check"></i></span>
                            <span class="menu-text">Monitor Absensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.psdm.leaves') }}" class="aside-menu-item {{ request()->routeIs('staff.psdm.leaves*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-calendar-minus"></i></span>
                            <span class="menu-text">Manajemen Cuti</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.psdm.business-trips') }}" class="aside-menu-item {{ request()->routeIs('staff.psdm.business-trips*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-briefcase"></i></span>
                            <span class="menu-text">Dinas Luar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.psdm.announcements') }}" class="aside-menu-item {{ request()->routeIs('staff.psdm.announcements*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-bullhorn"></i></span>
                            <span class="menu-text">Pengumuman</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.psdm.master-data') }}" class="aside-menu-item {{ request()->routeIs('staff.psdm.master-data*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-database"></i></span>
                            <span class="menu-text">Master Data</span>
                        </a>
                    </li>

                @elseif($role === 'staff_keuangan')
                    <li class="aside-menu-label">Keuangan</li>
                    <li>
                        <a href="{{ route('staff.keuangan.users') }}" class="aside-menu-item {{ request()->routeIs('staff.keuangan.users*') || request()->routeIs('staff.keuangan.salaries.input*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-edit"></i></span>
                            <span class="menu-text">Input Gaji</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.keuangan.salaries') }}" class="aside-menu-item {{ request()->routeIs('staff.keuangan.salaries') || request()->routeIs('staff.keuangan.salaries.show') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-money-bill-wave"></i></span>
                            <span class="menu-text">Data Gaji</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.keuangan.deductions.index') }}" class="aside-menu-item {{ request()->routeIs('staff.keuangan.deductions*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-minus-circle"></i></span>
                            <span class="menu-text">Jenis Potongan</span>
                        </a>
                    </li>


                @else
                    <li class="aside-menu-label">Menu</li>
                    <li>
                        <a href="{{ route('attendance.index') }}" class="aside-menu-item {{ request()->routeIs('attendance*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-clock"></i></span>
                            <span class="menu-text">Absensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user.salary') }}" class="aside-menu-item {{ request()->routeIs('user.salary*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-wallet"></i></span>
                            <span class="menu-text">Slip Gaji</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user.rekap') }}" class="aside-menu-item {{ request()->routeIs('user.rekap') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-chart-bar"></i></span>
                            <span class="menu-text">Rekap Absensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user.leaves') }}" class="aside-menu-item {{ request()->routeIs('user.leaves*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-calendar-minus"></i></span>
                            <span class="menu-text">Pengajuan Cuti</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user.business-trips') }}" class="aside-menu-item {{ request()->routeIs('user.business-trips*') ? 'active' : '' }}">
                            <span class="icon"><i class="fas fa-briefcase"></i></span>
                            <span class="menu-text">Dinas Luar</span>
                        </a>
                    </li>
                @endif
                
                {{-- Profile Section --}}
                <li class="aside-menu-label">Akun</li>
                <li>
                    <a href="{{ route('profile.edit') }}" class="aside-menu-item {{ request()->routeIs('profile*') ? 'active' : '' }}">
                        <span class="icon"><i class="fas fa-user-circle"></i></span>
                        <span class="menu-text">Profil</span>
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="w-full"
                          data-confirm="Apakah Anda yakin ingin keluar dari sistem?"
                          data-confirm-title="Konfirmasi Logout">
                        @csrf
                        <button type="submit" class="aside-menu-item w-full text-left">
                            <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                            <span class="menu-text">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        @endauth
    </div>
</aside>
