<x-guest-layout title="Konfirmasi Password">
    <div class="card">
        <div class="card-body">
            {{-- Header --}}
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Konfirmasi Password</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                    Silakan konfirmasi password Anda sebelum melanjutkan.
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
                    <div class="relative">
                        <span class="form-control-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            id="password"
                            type="password" 
                            name="password" 
                            class="form-control form-control-with-icon" 
                            required 
                            autofocus
                        >
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="form-field">
                    <button type="submit" class="btn btn-primary w-full py-2.5">
                        <i class="fas fa-check"></i>
                        <span>Konfirmasi</span>
                    </button>
                </div>
                
                @if(Route::has('password.request'))
                <div class="mt-4 text-center">
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                        Lupa password?
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>
</x-guest-layout>