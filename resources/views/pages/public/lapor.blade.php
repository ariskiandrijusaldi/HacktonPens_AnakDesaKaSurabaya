@extends('layouts.public')

@section('title', 'Buat Laporan Aman & Anonim — SATGAS PPKS')

@section('content')
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-12 lg:px-8">
        <div class="lg:col-span-8">
            <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-brand-700">Buat Laporan</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-ink-900 sm:text-5xl">Ceritakan dengan caramu.</h1>
            <p class="mt-4 max-w-2xl text-lg text-ink-600">
                Tidak perlu lengkap. Tulis sejauh yang kamu mampu saat ini. Kamu bisa berhenti kapan saja.
            </p>

            <div id="report-form-wrap" class="mt-8">
                <form id="report-form" class="space-y-8" novalidate enctype="multipart/form-data">
                    <div class="card border border-ink-100 p-6">
                        <label for="text" class="label text-base">Apa yang ingin kamu sampaikan?</label>
                        <p class="hint mb-3">Tulis semampumu. Tidak harus lengkap.</p>
                        <textarea id="text" name="text" rows="9" required minlength="20" maxlength="10000"
                                  class="input resize-y text-base leading-relaxed"
                                  placeholder="Tuangkan yang ada di pikiranmu. Tidak ada jawaban yang salah."></textarea>
                        <div class="mt-2 flex items-center justify-between text-sm">
                            <span id="text-error" class="field-error" role="alert" aria-live="polite"></span>
                            <span id="text-counter" class="ml-auto text-ink-500">0 / 10.000</span>
                        </div>
                    </div>

                    <fieldset class="card border border-ink-100 p-6">
                        <legend class="label text-base">Apa yang kamu harapkan?</legend>
                        <p class="hint mb-4">Pilih satu. Kamu bisa mengubah keputusan ini nanti.</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @php
                                $wishes = [
                                    'KONSULTASI' => ['Hanya ingin konsultasi', 'Kami mendengarkan dan memberi pilihan, tanpa menekan.'],
                                    'PENDAMPINGAN' => ['Ingin didampingi', 'Kami menemani langkah-langkah berikutnya.'],
                                    'PROSES_FORMAL' => ['Ingin proses formal', 'Kami bantu menempuh jalur resmi yang aman.'],
                                    'BELUM_TAHU' => ['Belum tahu', 'Tidak apa-apa. Kita cari tahu bersama.'],
                                ];
                            @endphp
                            @foreach ($wishes as $value => [$title, $desc])
                                <label class="flex cursor-pointer gap-3 rounded-2xl border-2 border-ink-200 p-4 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                    <input type="radio" name="victim_wish" value="{{ $value }}" required
                                           class="mt-1 h-5 w-5 shrink-0 accent-brand-700">
                                    <span>
                                        <span class="block font-semibold text-ink-900">{{ $title }}</span>
                                        <span class="mt-0.5 block text-sm text-ink-600">{{ $desc }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="card border border-ink-100 p-6">
                        <label for="location" class="label text-base">Di mana kira-kira kejadiannya? <span class="font-normal text-ink-400">(opsional)</span></label>
                        <input id="location" name="location" type="text" maxlength="200" class="input"
                               placeholder="Contoh: Gedung B, sekitar kantin">
                        <p class="hint">Tulis lokasi umum saja, jangan alamat lengkap. Ini membantu kami memahami konteks.</p>
                    </div>

                    <div class="card border border-ink-100 p-6">
                        <label for="contact" class="label text-base">Kontak yang bisa dihubungi <span class="font-normal text-ink-400">(opsional)</span></label>
                        <input id="contact" name="contact" type="text" maxlength="255" class="input"
                               placeholder="Nomor telepon atau email" autocomplete="off">
                        <div class="mt-3 rounded-xl bg-exit-500/10 p-4 text-sm text-ink-700">
                            <strong class="font-semibold">Dibaca dulu ya:</strong> Kontak hanya dibuka oleh Admin dalam keadaan darurat yang mengancam nyawa. Setiap pembukaan selalu tercatat di log audit dan tidak bisa dihapus.
                        </div>
                    </div>

                    <div class="card border border-ink-100 p-6">
                        <label for="files" class="label text-base">Lampiran <span class="font-normal text-ink-400">(opsional, maks 3)</span></label>
                        <input id="files" name="files" type="file" multiple accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                               class="block w-full cursor-pointer rounded-xl border border-dashed border-ink-300 p-3 text-sm file:mr-4 file:rounded-full file:border-0 file:bg-brand-700 file:px-5 file:py-2 file:font-semibold file:text-white">
                        <p class="hint">Jenis: JPG, PNG, atau PDF. Maksimal 5 MB per berkas. Metadata lokasi pada foto akan dihapus.</p>
                        <p id="files-error" class="field-error" role="alert" aria-live="polite"></p>
                        <ul id="file-preview" class="mt-4 space-y-2"></ul>
                    </div>

                    <div class="card border-2 border-brand-200 bg-brand-50/60 p-6">
                        <label class="flex cursor-pointer gap-3">
                            <input id="consent" name="consent" type="checkbox" required class="mt-1 h-5 w-5 shrink-0 accent-brand-700">
                            <span class="text-sm text-ink-700">
                                Saya setuju laporan ini dikirim ke layanan AI untuk disensor dan diprioritaskan. Data pribadi saya disamarkan, dan keputusan akhir tetap di tangan Satgas manusia.
                            </span>
                        </label>
                        <p id="consent-error" class="field-error" role="alert" aria-live="polite"></p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button type="submit" id="submit-button" class="btn-primary px-8 py-3.5 text-base">
                            <span data-submit-label>Kirim Laporan</span>
                            <svg data-submit-spinner viewBox="0 0 24 24" class="hidden h-5 w-5 animate-spin" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="3" opacity="0.25"/><path fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" d="M21 12a9 9 0 0 0-9-9"/></svg>
                        </button>
                        <p class="text-sm text-ink-500">Laporan terkirim tanpa menyimpan namamu.</p>
                    </div>
                </form>
            </div>

            <div id="report-success" class="hidden" aria-live="polite">
                <div class="card border-2 border-brand-600 p-7">
                    <div class="flex items-center gap-3">
                        <span class="grid h-12 w-12 place-items-center rounded-full bg-brand-700 text-white" aria-hidden="true">
                            <svg viewBox="0 0 20 20" class="h-7 w-7"><path fill="currentColor" d="M16.7 6.3 8.9 14a1 1 0 0 1-1.4 0l-3.2-3.2a1 1 0 1 1 1.4-1.4l2.5 2.5 7.1-7.1a1 1 0 0 1 1.4 1.4Z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-2xl font-extrabold text-ink-900">Laporan terkirim.</h2>
                            <p class="text-sm text-ink-600">Terima kasih sudah berani melangkah.</p>
                        </div>
                    </div>

                    <p class="mt-6 text-sm font-semibold uppercase tracking-wide text-ink-500">Kode tiket kamu</p>
                    <p id="ticket-code" class="mt-1 break-all text-3xl font-black tracking-tight text-brand-800 sm:text-4xl">—</p>

                    <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                        <button type="button" id="copy-ticket" class="btn-primary">Salin Kode</button>
                        <button type="button" id="download-ticket" class="btn-outline">Unduh (.txt)</button>
                        <a href="/tiket" class="btn-ghost">Cek Status</a>
                    </div>

                    <div class="mt-6 rounded-xl border-l-4 border-alert-600 bg-alert-50 p-4 text-sm text-alert-700">
                        <strong class="font-bold">Simpan kode ini.</strong> Tanpa kode ini kami tidak bisa menunjukkan status laporanmu, dan kode tidak bisa dipulihkan. Kode tidak disimpan di browser atau tautan mana pun.
                    </div>

                    <div class="mt-6 rounded-xl bg-ink-50 p-4 text-sm text-ink-600">
                        Dalam keadaan darurat, hubungi <a class="font-semibold text-alert-700 underline" href="tel:129" rel="noreferrer noopener">SAPA 129</a> atau <a class="font-semibold text-alert-700 underline" href="tel:110" rel="noreferrer noopener">110</a>.
                    </div>
                </div>
            </div>
        </div>

        <aside class="lg:col-span-4">
            <div class="sticky top-28 space-y-4">
                <div class="card border border-ink-100 p-6">
                    <h2 class="font-bold text-ink-900">Yang perlu kamu tahu</h2>
                    <ul class="mt-4 space-y-3 text-sm text-ink-600">
                        <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-brand-600"></span>Kamu boleh melapor tanpa nama.</li>
                        <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-brand-600"></span>Identitas disamarkan otomatis sebelum ditinjau.</li>
                        <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-brand-600"></span>Kamu menentukan langkah berikutnya.</li>
                        <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-brand-600"></span>Kami tidak menyimpan alamat IP.</li>
                    </ul>
                </div>
                <div class="card border border-brand-100 bg-brand-50/70 p-6">
                    <p class="font-bold text-ink-900">Butuh menutup halaman cepat?</p>
                    <p class="mt-2 text-sm text-ink-600">Tekan <kbd class="rounded border border-ink-300 bg-white px-1.5 py-0.5 font-mono text-xs">Esc</kbd> dua kali, atau tombol Keluar Cepat di kanan bawah.</p>
                </div>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/lapor.js')
@endpush
