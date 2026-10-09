<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="CampusCare — Ruang aman dan anonim untuk melaporkan kekerasan seksual di lingkungan kampus.">

    <title>@yield('title', 'CampusCare — Layanan Satgas PPKS Kampus')</title>

    @vite(['resources/css/campuscare.css'])
    @stack('head')
</head>
<body>
    <a href="#konten" class="skip-link">Lewati ke konten utama</a>

    <header class="public-header">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-mark" aria-hidden="true">♥</span>
            <span>
                <strong>CampusCare</strong>
                <small>Layanan Satgas PPKS Kampus</small>
            </span>
        </a>

        <nav class="public-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('lapor') }}">Lapor</a>
            <a href="{{ route('tiket') }}">Cek Status</a>
            <a href="{{ route('bantuan') }}">Bantuan</a>
        </nav>

        <a href="{{ route('lapor') }}" class="help-button">
            Buat Laporan
        </a>
    </header>

    <main id="konten" tabindex="-1">
        @yield('content')
    </main>

    <footer class="public-footer">
        <div class="brand-mark" aria-hidden="true">♥</div>
        <div class="footer-message">
            <strong>CampusCare</strong>
            <p>Kampus yang aman adalah tanggung jawab kita bersama.</p>
        </div>
        <div class="footer-links">
            <a href="{{ route('bantuan') }}">Kontak Darurat</a>
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('masuk') }}" rel="nofollow">Portal Petugas</a>
        </div>
    </footer>
</body>
</html>