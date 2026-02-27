<x-guest-layout title="Konfirmasi Password">
    <div class="card">
        <div class="card-body">
            {{-- Header --}}
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Konfirmasi Password</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                    Ini adalah area yang aman. Silakan konfirmasi password Anda untuk melanjutkan.
                </p>
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

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

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
                            autofocus
                        >
                        <button type="button" @click="show = !show" style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%);" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="form-field">
                    <button type="submit" class="btn btn-primary w-full py-2.5">
                        <i class="fas fa-check"></i>
                        <span>Konfirmasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
