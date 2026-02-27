<x-app-layout title="Profil">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                <i class="fas fa-user-circle text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
                <h1 class="page-title dark:page-title-dark">Profil Saya</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola informasi profil dan keamanan akun</p>
            </div>
        </div>
    </x-slot>

    {{-- Profile Photo & Info --}}
    <div class="card mb-6">
        <div class="card-body">
            @include('profile.partials.update-profile-photo-form')
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Profile Information --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-id-card text-blue-500 mr-2"></i>
                    Informasi Profil
                </h3>
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- Update Password --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-lock text-yellow-500 mr-2"></i>
                    Ubah Password
                </h3>
            </div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

</x-app-layout>
