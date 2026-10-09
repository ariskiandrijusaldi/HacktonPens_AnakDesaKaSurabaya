<!DOCTYPE html>
<html lang="id" class="scroll-pt-24">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f766e">
    <meta name="description" content="Ruang aman dan anonim untuk melaporkan kekerasan seksual di kampus. Kamu tidak sendirian.">
    <title>@yield('title', 'SATGAS PPKS Kampus — Aman. Anonim. Didampingi.')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-white text-ink-800">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[80] focus:rounded-full focus:bg-brand-700 focus:px-5 focus:py-3 focus:font-semibold focus:text-white">
        Lewati ke konten utama
    </a>

    <x-site-header />

    <main id="konten" tabindex="-1" class="scroll-mt-24 focus:outline-none">
        @yield('content')
    </main>

    <x-site-footer />
    <x-quick-exit />
    <x-toasts />

    @stack('scripts')
</body>
</html>
