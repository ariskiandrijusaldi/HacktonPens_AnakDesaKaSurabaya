<!DOCTYPE html>
<html lang="id" class="scroll-pt-24">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#eb0a1e">
    <title>@yield('title', 'Ruang Petugas — SATGAS PPKS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-ink-50 text-ink-800" data-guard="officer" data-officer-account="{{ session('campuscare_user.username') }}" data-officer-role="{{ strtoupper(session('campuscare_user.role', '')) }}">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[80] focus:rounded-full focus:bg-brand-700 focus:px-5 focus:py-3 focus:font-semibold focus:text-white">
        Lewati ke konten utama
    </a>

    <x-officer-header />

    <main id="konten" tabindex="-1" class="mx-auto max-w-[1400px] px-4 py-8 focus:outline-none sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <x-toasts />

    @stack('scripts')
</body>
</html>
