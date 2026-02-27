<x-guest-layout title="Lupa Password">
    <div class="card">
        <div class="card-body">
            {{-- Header --}}
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Lupa Password?</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                    Masukkan email Anda dan kami akan mengirimkan link reset password.
                </p>
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

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

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
                            placeholder="nama@email.com"
                            required 
                            autofocus
                        >
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="form-field">
                    <button type="submit" class="btn btn-primary w-full py-2.5">
                        <i class="fas fa-paper-plane"></i>
                        <span>Kirim Link Reset</span>
                    </button>
                </div>

                {{-- Back to Login --}}
                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
