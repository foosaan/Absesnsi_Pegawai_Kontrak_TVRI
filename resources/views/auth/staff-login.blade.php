<x-guest-layout title="Login Staff">
    <div class="card">
        <div class="card-body">
            {{-- Header --}}
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900">
                    <i class="fas fa-user-tie text-2xl text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Login Staff</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Masuk sebagai Staff PSDM / Keuangan</p>
            </div>

            {{-- Session Status --}}
            @if(session('status'))
                <div class="notification notification-success mb-6">
                    {{ session('status') }}
                </div>
            @endif

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

            <form method="POST" action="{{ route('staff.login') }}">
                @csrf
                <input type="hidden" name="login_type" value="staff">

                {{-- NIP --}}
                <div class="form-field">
                    <label for="nip" class="form-label">NIP</label>
                    <div class="relative">
                        <span class="form-control-icon">
                            <i class="fas fa-id-card"></i>
                        </span>
                        <input 
                            id="nip"
                            type="text" 
                            name="nip" 
                            value="{{ old('nip') }}"
                            class="form-control form-control-with-icon" 
                            placeholder="Masukkan NIP Anda"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            required 
                            autofocus
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
                            placeholder="••••••••"
                            required 
                        >
                        <button type="button" @click="show = !show" style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%);" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember Me --}}
                <div class="form-field">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-700"
                        >
                        <span class="text-sm text-gray-600 dark:text-slate-300">Ingat saya</span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <div class="form-field">
                    <button type="submit" class="btn btn-success w-full py-2.5">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk sebagai Staff</span>
                    </button>
                </div>

                {{-- Links --}}
                <div class="mt-4 flex flex-col items-center gap-2 text-sm">
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">
                            Lupa password?
                        </a>
                    @endif
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
                        <i class="fas fa-arrow-left mr-1"></i> Login sebagai User
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
