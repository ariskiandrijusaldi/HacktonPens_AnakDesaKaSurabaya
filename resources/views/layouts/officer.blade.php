<!DOCTYPE html>
<html lang="id" class="scroll-pt-24">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f766e">
    <title>@yield('title', 'Ruang Petugas — SATGAS PPKS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-ink-50 text-ink-800" data-guard="officer">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[80] focus:rounded-full focus:bg-brand-700 focus:px-5 focus:py-3 focus:font-semibold focus:text-white">
        Lewati ke konten utama
    </a>

    <x-officer-header />

    <div id="alarm-banner" class="hidden" role="alert" aria-live="assertive">
        <div class="alarm-banner flex items-center gap-3 bg-alert-600 px-4 py-3 text-sm font-bold text-white sm:px-8">
            <svg viewBox="0 0 20 20" class="h-5 w-5 shrink-0" aria-hidden="true"><path fill="currentColor" d="M10 2 1.5 17h17L10 2Zm0 5.5a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Zm0 7a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"/></svg>
            <span data-alarm-banner-text>Laporan DARURAT baru masuk.</span>
            <button type="button" data-alarm-dismiss class="ml-auto rounded-full bg-white/20 px-3 py-1 text-xs font-semibold hover:bg-white/30">Tutup</button>
        </div>
    </div>

    <main id="konten" tabindex="-1" class="mx-auto max-w-[1400px] px-4 py-8 focus:outline-none sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <x-toasts />

    @stack('scripts')
</body>
</html>
