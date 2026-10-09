@extends('layouts.public')

@section('title', 'SATGAS PPKS Kampus — Kamu Tidak Sendirian')

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-b from-brand-50 via-brand-50/60 to-white">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <svg class="absolute -right-24 top-0 h-full w-[70%] max-w-[900px] opacity-70" viewBox="0 0 900 700" fill="none" preserveAspectRatio="xMaxYMid slice">
                <defs>
                    <linearGradient id="heroGrad" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#99f6e4"/>
                        <stop offset="1" stop-color="#0f766e"/>
                    </linearGradient>
                </defs>
                <path d="M120 700V300l120-70 120 70v400H120Z" fill="url(#heroGrad)" opacity="0.18"/>
                <path d="M380 700V220l150-90 150 90v480H380Z" fill="url(#heroGrad)" opacity="0.14"/>
                <path d="M660 700V360l110-64 110 64v340H660Z" fill="url(#heroGrad)" opacity="0.12"/>
                <g fill="#0f766e" opacity="0.14">
                    <rect x="170" y="360" width="40" height="40" rx="6"/><rect x="170" y="440" width="40" height="40" rx="6"/>
                    <rect x="430" y="290" width="44" height="44" rx="6"/><rect x="500" y="290" width="44" height="44" rx="6"/>
                    <rect x="430" y="380" width="44" height="44" rx="6"/><rect x="500" y="380" width="44" height="44" rx="6"/>
                    <rect x="700" y="420" width="40" height="40" rx="6"/><rect x="770" y="420" width="40" height="40" rx="6"/>
                </g>
            </svg>
        </div>

        <div class="relative mx-auto grid max-w-7xl gap-12 px-4 pb-28 pt-16 sm:px-6 lg:grid-cols-12 lg:pb-32 lg:pt-20 lg:px-8">
            <div class="lg:col-span-7">
                <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-brand-700">SATGAS PPKS Kampus</p>
                <h1 class="mt-4 text-5xl font-black uppercase leading-[0.95] tracking-tight text-ink-900 sm:text-6xl lg:text-7xl">
                    Kamu Tidak<br>Sendirian
                </h1>
                <p class="mt-5 max-w-xl text-2xl font-bold italic text-brand-800 sm:text-3xl">
                    Kamu yang menentukan langkahnya.
                </p>
                <p class="mt-5 max-w-xl text-base leading-relaxed text-ink-600 sm:text-lg">
                    Lapor tanpa perlu menyebut namamu. Kami mendampingi dengan tenang, menghormati privasimu, dan menempatkan keselamatanmu lebih dulu.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="/lapor" class="btn-primary px-7 py-3.5 text-base">
                        <svg viewBox="0 0 20 20" class="h-5 w-5" aria-hidden="true"><path fill="currentColor" d="M4 3h12a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H8l-4 4V4a1 1 0 0 1 1-1Z"/></svg>
                        Lapor Secara Aman &amp; Anonim
                    </a>
                    <a href="/tiket" class="btn-outline px-7 py-3.5 text-base">Cek Status Laporan</a>
                </div>
                <p class="mt-4 text-sm text-ink-500">
                    Butuh menutup halaman ini cepat? Tekan <kbd class="rounded border border-ink-300 bg-white px-1.5 py-0.5 font-mono text-xs">Esc</kbd> dua kali, atau pakai tombol Keluar Cepat.
                </p>
            </div>

            <div class="relative lg:col-span-5">
                <div class="lg:absolute lg:bottom-0 lg:right-0 lg:w-[22rem]">
                    <div class="card w-full border border-brand-100 p-5">
                        <div class="flex items-center gap-3">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-exit-500 text-ink-900" aria-hidden="true">
                                <svg viewBox="0 0 20 20" class="h-6 w-6"><path fill="currentColor" d="M10 2a5 5 0 0 0-5 5v3l-1.6 2.6A1 1 0 0 0 4.3 14h11.4a1 1 0 0 0 .9-1.4L15 10V7a5 5 0 0 0-5-5Zm0 16a2.5 2.5 0 0 0 2.4-1.8H7.6A2.5 2.5 0 0 0 10 18Z"/></svg>
                            </span>
                            <p class="font-bold text-ink-900">Butuh bantuan sekarang?</p>
                        </div>
                        <p class="mt-3 text-sm text-ink-600">Hubungi layanan SAPA untuk pendampingan segera, kapan pun.</p>
                        <a href="tel:129" rel="noreferrer noopener" class="btn-outline mt-4 w-full">Hubungi SAPA 129</a>
                        <p class="mt-2 text-center text-xs text-ink-500">Dalam bahaya langsung? Hubungi <a class="font-semibold text-alert-700 underline" href="tel:110" rel="noreferrer noopener">110</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="relative z-10 mx-auto -mt-16 max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Keunggulan layanan">
        <ul class="flex snap-x gap-4 overflow-x-auto pb-4 lg:grid lg:grid-cols-6 lg:overflow-visible">
            @php
                $features = [
                    ['100% Anonim', 'Tanpa akun, tanpa nama', 'shield', 'text-brand-700'],
                    ['Identitas Disensor', 'Disamarkan otomatis', 'mask', 'text-brand-700'],
                    ['Prioritas Cerdas', 'Dibantu triase AI', 'bolt', 'text-exit-600'],
                    ['Ditangani Manusia', 'Satgas memutuskan akhir', 'hand', 'text-brand-700'],
                    ['Pendampingan', 'Kamu tidak sendiri', 'heart', 'text-brand-700'],
                    ['Bantuan 24 Jam', 'Selalu ada yang siaga', 'clock', 'text-brand-700'],
                ];
            @endphp
            @foreach ($features as [$title, $subtitle, $icon, $color])
                <li class="card min-w-[15rem] snap-start border border-ink-100 p-5 transition-transform duration-200 hover:-translate-y-1.5 motion-reduce:transform-none lg:min-w-0">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-brand-50 {{ $color }}" aria-hidden="true">
                        @switch($icon)
                            @case('shield')
                                <svg viewBox="0 0 20 20" class="h-6 w-6"><path fill="currentColor" d="M10 2 3 4.5v5c0 4 2.8 7.7 7 8.5 4.2-.8 7-4.5 7-8.5v-5L10 2Z"/></svg>
                                @break
                            @case('mask')
                                <svg viewBox="0 0 20 20" class="h-6 w-6"><path fill="currentColor" d="M10 3c3.3 0 6 1.3 6 3v2c0 3.3-2.7 6-6 6s-6-2.7-6-6V6c0-1.7 2.7-3 6-3Zm-2.5 4a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm5 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/></svg>
                                @break
                            @case('bolt')
                                <svg viewBox="0 0 20 20" class="h-6 w-6"><path fill="currentColor" d="M11 1 3 11h5l-1 8 9-11h-5l1-7Z"/></svg>
                                @break
                            @case('hand')
                                <svg viewBox="0 0 20 20" class="h-6 w-6"><path fill="currentColor" d="M6 9V5a1.3 1.3 0 1 1 2.6 0v4h.8V3.3a1.3 1.3 0 1 1 2.6 0V9h.8V4.3a1.3 1.3 0 1 1 2.6 0v6.2c0 3.6-2.2 6.5-5.5 6.5-2.4 0-4.2-1.4-5-3.6L4.9 11A1.3 1.3 0 0 1 6 9Z"/></svg>
                                @break
                            @case('heart')
                                <svg viewBox="0 0 20 20" class="h-6 w-6"><path fill="currentColor" d="M10 17S3 13.2 3 8.2A3.7 3.7 0 0 1 10 6a3.7 3.7 0 0 1 7 2.2C17 13.2 10 17 10 17Z"/></svg>
                                @break
                            @default
                                <svg viewBox="0 0 20 20" class="h-6 w-6"><path fill="currentColor" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm.8 4v4.2l3 1.8-.8 1.3-3.7-2.2V6h1.5Z"/></svg>
                        @endswitch
                    </span>
                    <h3 class="mt-4 text-sm font-extrabold uppercase tracking-wide {{ $color }}">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-ink-600">{{ $subtitle }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    <section id="cara-kerja" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-20 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-brand-700">Cara Kerja</p>
            <h2 class="section-title mt-3">Melapor itu sederhana, dan kamu pegang kendali.</h2>
        </div>
        <ol class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            @php
                $steps = [
                    ['Tulis dengan tenang', 'Ceritakan sejauh yang kamu mampu. Tidak harus lengkap, tidak harus rapi.'],
                    ['Identitas disamarkan', 'Nama, kontak, dan detail pribadi disensor otomatis sebelum ditinjau.'],
                    ['AI memprioritaskan', 'Triase membantu menandai laporan yang paling mendesak.'],
                    ['Satgas mendampingi', 'Manusia yang memutuskan dan menghubungimu sesuai keinginanmu.'],
                ];
            @endphp
            @foreach ($steps as $index => $step)
                <li class="card border border-ink-100 p-6">
                    <span class="grid h-11 w-11 place-items-center rounded-full bg-brand-700 text-lg font-black text-white">{{ $index + 1 }}</span>
                    <h3 class="mt-4 text-lg font-bold text-ink-900">{{ $step[0] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ $step[1] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section id="transparansi" class="scroll-mt-24 bg-brand-50/70 py-20">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-brand-700">Transparansi</p>
                <h2 class="section-title mt-3">Aman secara desain, bukan sekadar janji.</h2>
                <p class="mt-4 max-w-xl text-ink-600">
                    Kami hanya meminta informasi yang benar-benar dibutuhkan. Teknologi membantu menyaring, tetapi keputusan akhir selalu ada di tangan Satgas.
                </p>
            </div>
            <ul class="space-y-4">
                @php
                    $points = [
                        ['Identitas disensor otomatis', 'Nama dan detail pengenal disamarkan sebelum masuk ke sistem petugas.'],
                        ['Tanpa akun, tanpa jejak', 'Kamu tidak perlu mendaftar. Tiket anonim adalah kuncinya.'],
                        ['Tanpa menyimpan IP', 'Kami tidak mencatat alamat IP atau memasang pelacak pihak ketiga.'],
                        ['AI membantu, manusia memutuskan', 'Skor urgensi adalah saran. Satgas yang menangani dan mendampingi.'],
                    ];
                @endphp
                @foreach ($points as $point)
                    <li class="card flex gap-4 border border-ink-100 p-5">
                        <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-full bg-brand-700 text-white" aria-hidden="true">
                            <svg viewBox="0 0 20 20" class="h-5 w-5"><path fill="currentColor" d="M16.7 6.3 8.9 14a1 1 0 0 1-1.4 0l-3.2-3.2a1 1 0 1 1 1.4-1.4l2.5 2.5 7.1-7.1a1 1 0 0 1 1.4 1.4Z"/></svg>
                        </span>
                        <div>
                            <h3 class="font-bold text-ink-900">{{ $point[0] }}</h3>
                            <p class="mt-1 text-sm text-ink-600">{{ $point[1] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="card overflow-hidden border border-brand-100 bg-gradient-to-br from-brand-700 to-brand-900 p-8 text-center text-white sm:p-14">
            <h2 class="text-3xl font-black uppercase leading-tight sm:text-4xl">Langkah kecilmu berarti.</h2>
            <p class="mx-auto mt-4 max-w-xl text-brand-100">
                Kamu boleh melapor tanpa yakin sepenuhnya. Kami akan berjalan bersamamu dari titik mana pun.
            </p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="/lapor" class="btn bg-white px-7 py-3.5 text-base text-brand-800 hover:bg-brand-100">Mulai Lapor</a>
                <a href="/bantuan" class="btn border-2 border-white/70 px-7 py-3.5 text-base text-white hover:bg-white/10">Lihat Bantuan</a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    @vite('resources/js/pages/landing.js')
@endpush
