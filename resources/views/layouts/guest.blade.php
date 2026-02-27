<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'TVRI Absensi' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-inter antialiased bg-gray-50 dark:bg-slate-800">
    <div class="flex min-h-screen flex-col items-center justify-center p-6">
        {{-- Logo/Brand --}}
        <div class="mb-8 flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-2xl font-bold text-white shadow-lg">
                T
            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white">TVRI</h1>
                <p class="text-xs text-gray-500 dark:text-slate-400">Sistem Absensi</p>
            </div>
        </div>

        {{-- Card Content --}}
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        <div class="mt-8 text-center text-sm text-gray-500 dark:text-slate-400">
            <p>&copy; {{ date('Y') }} TVRI. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
