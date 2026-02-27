<x-guest-layout title="Verifikasi Email">
    <div class="card">
        <div class="card-body text-center">
            {{-- Icon --}}
            <div class="mb-6">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <i class="fas fa-envelope-open text-3xl text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>

            {{-- Header --}}
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Verifikasi Email Anda</h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-slate-400">
                Kami telah mengirimkan link verifikasi ke email Anda. Silakan cek inbox Anda.
            </p>

            {{-- Session Status --}}
            @if(session('status') == 'verification-link-sent')
                <div class="notification notification-success mt-6">
                    Link verifikasi baru telah dikirim ke email Anda.
                </div>
            @endif

            <div class="mt-6 flex flex-col gap-3">
                {{-- Resend --}}
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full py-2.5">
                        <i class="fas fa-redo"></i>
                        <span>Kirim Ulang Link</span>
                    </button>
                </form>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary w-full">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>