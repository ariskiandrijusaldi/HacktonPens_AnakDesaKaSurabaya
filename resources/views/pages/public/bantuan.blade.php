@extends('layouts.public')

@section('title', 'Bantuan & Kontak Darurat — SATGAS PPKS Kampus')

@section('content')
    <section class="border-b border-ink-100 bg-brand-50/60">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-brand-700">Bantuan</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-ink-900 sm:text-5xl">Kamu tidak harus menghadapinya sendiri.</h1>
            <p class="mt-4 max-w-2xl text-lg text-ink-600">
                Kalau kamu sedang dalam bahaya atau merasa tidak aman sekarang, hubungi layanan darurat di bawah. Nomor ini gratis dan bisa diakses kapan saja.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" aria-labelledby="kontak-darurat">
        <div class="flex items-center justify-between gap-4">
            <h2 id="kontak-darurat" class="text-2xl font-extrabold text-ink-900">Kontak Darurat &amp; Pendampingan</h2>
            <button type="button" data-help-retry class="btn-ghost btn-sm hidden">Muat ulang</button>
        </div>

        <div data-help-status class="mt-4 text-sm text-ink-500" role="status" aria-live="polite">Memuat kontak…</div>

        <div data-help-cards class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="skeleton h-40"></div>
            <div class="skeleton h-40"></div>
            <div class="skeleton h-40"></div>
        </div>

        <template data-help-template>
            <article class="card flex flex-col border border-ink-100 p-6">
                <h3 data-help-name class="text-lg font-bold text-ink-900"></h3>
                <p data-help-description class="mt-2 flex-1 text-sm leading-relaxed text-ink-600"></p>
                <p data-help-hours class="mt-3 text-xs font-semibold uppercase tracking-wide text-brand-700"></p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a data-help-phone href="#" rel="noreferrer noopener" class="btn-primary btn-sm">Telepon</a>
                    <a data-help-url href="#" target="_blank" rel="noreferrer noopener" class="btn-outline btn-sm">Buka Situs</a>
                </div>
            </article>
        </template>
    </section>

    <section id="p3" class="scroll-mt-24 bg-brand-50/60 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-extrabold text-ink-900">Pertolongan Pertama Psikologis</h2>
            <p class="mt-3 max-w-2xl text-ink-600">
                Ini bukan terapi, tetapi langkah awal yang bisa membantu menenangkan diri atau orang di sekitarmu.
            </p>
            <ol class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $p3 = [
                        ['Lihat', 'Perhatikan kondisi tanpa menghakimi. Dekatkan diri dengan tenang.'],
                        ['Dengar', 'Dengarkan tanpa memotong. Tidak perlu buru-buru menasihati.'],
                        ['Lindungi', 'Jauhkan dari situasi yang mengancam dan jaga privasinya.'],
                        ['Hubungkan', 'Bantu menghubungi layanan pendamping bila ia mengizinkan.'],
                        ['Tenangkan', 'Napas lambat, bicara pelan, hindari pertanyaan yang menekan.'],
                        ['Jaga dirimu', 'Pendamping juga butuh jeda. Minta bantuan bila perlu.'],
                    ];
                @endphp
                @foreach ($p3 as $index => $item)
                    <li class="card border border-ink-100 p-5">
                        <span class="text-sm font-black text-brand-700">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-1 text-lg font-bold text-ink-900">{{ $item[0] }}</h3>
                        <p class="mt-1 text-sm text-ink-600">{{ $item[1] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">
            <div>
                <h2 class="text-2xl font-extrabold text-ink-900">Apa yang terjadi setelah aku melapor?</h2>
                <ol class="mt-6 space-y-4">
                    @php
                        $after = [
                            'Laporanmu disensor otomatis: identitas dan detail pribadi disamarkan.',
                            'Triase AI membantu menandai tingkat urgensi sebagai saran awal.',
                            'Satgas manusia meninjau, memutuskan, dan mulai mendampingi.',
                            'Kamu memantau perkembangan lewat kode tiket tanpa perlu menyebut nama.',
                        ];
                    @endphp
                    @foreach ($after as $index => $item)
                        <li class="flex gap-4">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-100 font-bold text-brand-800">{{ $index + 1 }}</span>
                            <p class="pt-1.5 text-ink-700">{{ $item }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div id="hak" class="scroll-mt-24 card border border-ink-100 p-7">
                <h2 class="text-2xl font-extrabold text-ink-900">Hak &amp; Perlindungan</h2>
                <ul class="mt-5 space-y-3 text-ink-700">
                    @php
                        $rights = [
                            'Kamu berhak melapor tanpa menyebut identitas.',
                            'Kamu berhak didampingi dan merasa aman.',
                            'Kamu berhak menentukan langkah, termasuk menolak proses formal.',
                            'Kamu berhak atas kerahasiaan dan penanganan yang tidak menghakimi.',
                        ];
                    @endphp
                    @foreach ($rights as $right)
                        <li class="flex gap-3">
                            <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-700 text-white" aria-hidden="true">
                                <svg viewBox="0 0 20 20" class="h-4 w-4"><path fill="currentColor" d="M16.7 6.3 8.9 14a1 1 0 0 1-1.4 0l-3.2-3.2a1 1 0 1 1 1.4-1.4l2.5 2.5 7.1-7.1a1 1 0 0 1 1.4 1.4Z"/></svg>
                            </span>
                            <span>{{ $right }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6 flex flex-col gap-2 sm:flex-row">
                    <a href="/lapor" class="btn-primary">Buat Laporan</a>
                    <a href="/tiket" class="btn-outline">Cek Tiket</a>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    @vite('resources/js/pages/bantuan.js')
@endpush
