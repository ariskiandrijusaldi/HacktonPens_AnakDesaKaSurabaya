@php
    $path = trim(request()->path(), '/');
    $isHome = $path === '';
    $isLapor = $path === 'lapor';
    $isTiket = $path === 'tiket';
    $isBantuan = $path === 'bantuan';
@endphp

<header class="sticky top-0 z-50 border-b border-ink-100 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/85">
    <div class="mx-auto flex h-20 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:h-[88px] lg:gap-6 lg:px-8">
        <a href="/" class="shrink-0" aria-label="SATGAS PPKS, kembali ke beranda">
            <x-logo />
        </a>

        <nav class="ml-2 hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
            <a href="/" class="nav-link {{ $isHome ? 'text-brand-700' : '' }}" @if ($isHome) aria-current="page" @endif>Beranda</a>
            <a href="/#cara-kerja" class="nav-link">Cara Kerja</a>

            <div class="relative" data-dropdown>
                <button type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="true"
                        class="nav-link {{ $isLapor || $isTiket ? 'text-brand-700' : '' }}">
                    Lapor
                    <svg data-chevron viewBox="0 0 20 20" class="h-4 w-4 transition-transform duration-150" aria-hidden="true"><path fill="currentColor" d="M5.3 7.3a1 1 0 0 1 1.4 0L10 10.6l3.3-3.3a1 1 0 1 1 1.4 1.4l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 0-1.4Z"/></svg>
                </button>
                <div data-dropdown-panel class="absolute left-0 top-full z-50 mt-2 hidden w-60 rounded-2xl border border-ink-100 bg-white p-2 shadow-pop">
                    <a href="/lapor" class="block rounded-xl px-4 py-2.5 text-[15px] font-medium text-ink-700 hover:bg-brand-50 hover:text-brand-700">Buat Laporan</a>
                    <a href="/tiket" class="block rounded-xl px-4 py-2.5 text-[15px] font-medium text-ink-700 hover:bg-brand-50 hover:text-brand-700">Cek Tiket</a>
                </div>
            </div>

            <div class="relative" data-dropdown>
                <button type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="true" class="nav-link">
                    Hak &amp; Perlindungan
                    <svg data-chevron viewBox="0 0 20 20" class="h-4 w-4 transition-transform duration-150" aria-hidden="true"><path fill="currentColor" d="M5.3 7.3a1 1 0 0 1 1.4 0L10 10.6l3.3-3.3a1 1 0 1 1 1.4 1.4l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 0-1.4Z"/></svg>
                </button>
                <div data-dropdown-panel class="absolute left-0 top-full z-50 mt-2 hidden w-64 rounded-2xl border border-ink-100 bg-white p-2 shadow-pop">
                    <a href="/#transparansi" class="block rounded-xl px-4 py-2.5 text-[15px] font-medium text-ink-700 hover:bg-brand-50 hover:text-brand-700">Identitas &amp; Privasi</a>
                    <a href="/bantuan#hak" class="block rounded-xl px-4 py-2.5 text-[15px] font-medium text-ink-700 hover:bg-brand-50 hover:text-brand-700">Hak Pelapor</a>
                </div>
            </div>

            <div class="relative" data-dropdown>
                <button type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="true"
                        class="nav-link {{ $isBantuan ? 'text-brand-700' : '' }}">
                    Bantuan
                    <svg data-chevron viewBox="0 0 20 20" class="h-4 w-4 transition-transform duration-150" aria-hidden="true"><path fill="currentColor" d="M5.3 7.3a1 1 0 0 1 1.4 0L10 10.6l3.3-3.3a1 1 0 1 1 1.4 1.4l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 0-1.4Z"/></svg>
                </button>
                <div data-dropdown-panel class="absolute left-0 top-full z-50 mt-2 hidden w-64 rounded-2xl border border-ink-100 bg-white p-2 shadow-pop">
                    <a href="/bantuan" class="block rounded-xl px-4 py-2.5 text-[15px] font-medium text-ink-700 hover:bg-brand-50 hover:text-brand-700">Kontak Darurat</a>
                    <a href="/bantuan#p3" class="block rounded-xl px-4 py-2.5 text-[15px] font-medium text-ink-700 hover:bg-brand-50 hover:text-brand-700">Pertolongan Pertama Psikologis</a>
                </div>
            </div>
        </nav>

        <div class="ml-auto flex items-center gap-2">
            <button type="button" data-quick-exit class="btn-exit btn-sm hidden sm:inline-flex">
                <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path fill="currentColor" d="M10 2a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1Zm5.7 1.7a1 1 0 0 1 1.4 0A8.5 8.5 0 1 1 3 4a1 1 0 1 1 1.4 1.4A6.5 6.5 0 1 0 16 5a1 1 0 0 1-.3-1.3Z"/></svg>
                Keluar Cepat
            </button>

            <a href="/masuk" class="btn-outline btn-sm">
                <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path fill="currentColor" d="M10 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0 10c3.3 0 6 1.8 6 4v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-1c0-2.2 2.7-4 6-4Z"/></svg>
                <span class="hidden sm:inline">Masuk Petugas</span>
                <span class="sm:hidden">Masuk</span>
            </a>

            <button type="button" data-mobile-toggle aria-expanded="false" aria-controls="menu-mobile"
                    class="grid h-11 w-11 place-items-center rounded-full text-ink-700 hover:bg-ink-100 lg:hidden"
                    aria-label="Buka menu">
                <svg viewBox="0 0 20 20" class="h-6 w-6" aria-hidden="true"><path fill="currentColor" d="M3 5a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1Zm0 5a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1Zm1 4a1 1 0 1 0 0 2h12a1 1 0 1 0 0-2H4Z"/></svg>
            </button>
        </div>
    </div>

    <div id="menu-mobile" data-mobile-panel class="hidden border-t border-ink-100 bg-white lg:hidden">
        <nav class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-4" aria-label="Navigasi seluler">
            <a href="/" class="nav-link">Beranda</a>
            <a href="/#cara-kerja" class="nav-link">Cara Kerja</a>
            <a href="/lapor" class="nav-link">Buat Laporan</a>
            <a href="/tiket" class="nav-link">Cek Tiket</a>
            <a href="/bantuan" class="nav-link">Bantuan</a>
            <a href="/bantuan#hak" class="nav-link">Hak &amp; Perlindungan</a>
            <a href="/masuk" class="nav-link font-semibold text-brand-700">Masuk Petugas</a>
            <button type="button" data-quick-exit class="btn-exit mt-2 w-full">Keluar Cepat</button>
        </nav>
    </div>
</header>
