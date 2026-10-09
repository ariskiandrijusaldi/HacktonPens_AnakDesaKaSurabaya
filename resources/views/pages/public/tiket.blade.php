@extends('layouts.public')

@section('title', 'Cek Status Laporan — SATGAS PPKS Kampus')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-brand-700">Cek Tiket</p>
        <h1 class="mt-3 text-4xl font-black tracking-tight text-ink-900 sm:text-5xl">Pantau laporanmu.</h1>
        <p class="mt-4 text-lg text-ink-600">
            Masukkan kode tiket yang kamu terima saat melapor. Kode ini adalah satu-satunya kunci untuk melihat statusmu.
        </p>

        <form id="ticket-form" class="mt-8" novalidate>
            <label for="ticket-input" class="label text-base">Kode tiket</label>
            <div class="flex flex-col gap-3 sm:flex-row">
                <input id="ticket-input" name="ticket" type="text" inputmode="text" autocomplete="off" spellcheck="false"
                       class="input font-mono uppercase tracking-wider" placeholder="PPKS-XXXX-XXXX-XXXX" aria-describedby="ticket-help">
                <button type="submit" class="btn-primary shrink-0 px-7">
                    <span data-ticket-label>Cek Status</span>
                    <svg data-ticket-spinner viewBox="0 0 24 24" class="hidden h-5 w-5 animate-spin" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="3" opacity="0.25"/><path fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" d="M21 12a9 9 0 0 0-9-9"/></svg>
                </button>
            </div>
            <p id="ticket-help" class="hint">Huruf besar/kecil tidak masalah. Boleh tanpa tanda hubung.</p>
            <p id="ticket-error" class="field-error" role="alert" aria-live="assertive"></p>
        </form>

        <div id="ticket-loading" class="mt-8 hidden">
            <x-skeleton :rows="3" />
        </div>

        <section id="ticket-result" class="mt-8 hidden" aria-live="polite">
            <div class="card border border-ink-100 p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Kode Tiket</p>
                        <p id="result-code" class="font-mono text-lg font-bold text-ink-900">—</p>
                    </div>
                    <p id="result-updated" class="text-sm text-ink-500">—</p>
                </div>

                <div class="mt-6 border-t border-ink-100 pt-6">
                    <x-stepper :active="1" />
                </div>

                <div class="mt-6 rounded-xl bg-ink-50 p-4 text-sm text-ink-600">
                    <span class="font-semibold text-ink-800">Status saat ini:</span>
                    <span id="result-status" class="ml-1 font-bold text-brand-700">—</span>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-xl font-extrabold text-ink-900">Pesan dari Satgas</h2>
                <p class="mt-1 text-sm text-ink-500">Halaman ini menyegarkan otomatis setiap 15 detik selama tab terbuka.</p>
                <ul id="messages" class="mt-4 space-y-3"></ul>
                <p id="messages-empty" class="mt-4 hidden rounded-xl border border-dashed border-ink-300 p-5 text-center text-sm text-ink-500">
                    Belum ada pesan. Satgas akan menghubungimu di sini.
                </p>
            </div>

            <form id="reply-form" class="mt-8 card border border-ink-100 p-6" novalidate>
                <label for="reply-body" class="label text-base">Balas Satgas</label>
                <textarea id="reply-body" rows="4" maxlength="2000" class="input resize-y"
                          placeholder="Tulis balasan atau informasi tambahan (maks 2000 karakter)."></textarea>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-sm text-ink-500" id="reply-counter">0 / 2.000</span>
                    <button type="submit" class="btn-primary">Kirim Balasan</button>
                </div>
                <p id="reply-error" class="field-error" role="alert" aria-live="polite"></p>
            </form>
        </section>

        <div class="mt-10 rounded-xl border-l-4 border-exit-500 bg-exit-500/10 p-4 text-sm text-ink-700">
            Dalam keadaan darurat, jangan menunggu balasan. Hubungi <a class="font-semibold text-alert-700 underline" href="tel:129" rel="noreferrer noopener">SAPA 129</a> atau <a class="font-semibold text-alert-700 underline" href="tel:110" rel="noreferrer noopener">110</a>.
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/tiket.js')
@endpush
