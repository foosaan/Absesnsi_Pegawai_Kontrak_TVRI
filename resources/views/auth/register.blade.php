<x-guest-layout title="Register">
    <div class="card">
        <div class="card-body">
            {{-- Header --}}
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Daftar Akun</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Buat akun baru Anda</p>
            </div>

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="notification notification-danger mb-6">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                {{-- Name --}}
                <div class="form-field">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <div class="relative">
                        <span class="form-control-icon">
                            <i class="fas fa-user"></i>
                        </span>
                        <input 
                            id="name"
                            type="text" 
                            name="name" 
                            value="{{ old('name') }}"
                            class="form-control form-control-with-icon" 
                            required 
                            autofocus
                        >
                    </div>
                </div>

                {{-- Email --}}
                <div class="form-field">
                    <label for="email" class="form-label">Email</label>
                    <div class="relative">
                        <span class="form-control-icon">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input 
                            id="email"
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            class="form-control form-control-with-icon" 
                            required 
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div class="form-field">
                    <label for="password" class="form-label">Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <span class="form-control-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            id="password"
                            :type="show ? 'text' : 'password'" 
                            name="password" 
                            class="form-control form-control-with-icon pr-10" 
                            required 
                        >
                        <button type="button" @click="show = !show" style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%);" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div class="form-field">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <span class="form-control-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            id="password_confirmation"
                            :type="show ? 'text' : 'password'" 
                            name="password_confirmation" 
                            class="form-control form-control-with-icon pr-10" 
                            required 
                        >
                        <button type="button" @click="show = !show" style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%);" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="form-field">
                    <button type="submit" class="btn btn-primary w-full py-2.5">
                        <i class="fas fa-user-plus"></i>
                        <span>Daftar</span>
                    </button>
                </div>

                {{-- Login Link --}}
                <div class="mt-4 text-center">
                    <span class="text-sm text-gray-500 dark:text-slate-400">Sudah punya akun?</span>
                    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 ml-1">
                        Masuk
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>