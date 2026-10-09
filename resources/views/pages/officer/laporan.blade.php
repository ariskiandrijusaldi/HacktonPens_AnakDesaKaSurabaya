@extends('layouts.officer')

@section('title', 'Detail Laporan — SATGAS PPKS')

@section('content')
    <div data-report-root data-report-id="{{ $reportId }}">
        <a href="/dasbor" class="btn-ghost btn-sm -ml-2">
            <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path fill="currentColor" d="M12.7 4.3a1 1 0 0 1 0 1.4L7.4 11H17a1 1 0 1 1 0 2H7.4l5.3 5.3a1 1 0 0 1-1.4 1.4l-7-7a1 1 0 0 1 0-1.4l7-7a1 1 0 0 1 1.4 0Z"/></svg>
            Kembali ke Dasbor
        </a>

        <div id="detail-loading" class="mt-4">
            <x-skeleton :rows="4" />
        </div>

        <div id="detail-error" class="mt-4 hidden rounded-2xl border border-alert-200 bg-alert-50 p-6 text-alert-700" role="alert" aria-live="assertive"></div>

        <div id="detail-content" class="mt-4 hidden">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Kode Tiket</p>
                    <p id="d-ticket" class="font-mono text-2xl font-black text-ink-900">—</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span data-d-urgency-final></span>
                    <span data-d-status></span>
                    <span id="d-aifailed-badge" class="hidden inline-flex items-center gap-1 rounded-full border border-alert-500 bg-alert-50 px-3 py-1.5 text-sm font-bold text-alert-700">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path fill="currentColor" d="M10 2 1.5 17h17L10 2Zm0 5.5a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Zm0 7a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"/></svg>
                        Triase AI gagal
                    </span>
                </div>
            </div>

            <div id="d-aifailed-note" class="mt-4 hidden rounded-xl border-l-4 border-alert-600 bg-alert-50 p-4 text-sm text-alert-700">
                Triase AI gagal, urgensi diatur aman ke KUNING; mohon tinjau manual.
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <section class="card border border-ink-100 p-6">
                        <h2 class="text-lg font-extrabold text-ink-900">Ringkasan</h2>
                        <p id="d-summary" class="mt-3 text-ink-700">—</p>
                        <h2 class="mt-6 text-lg font-extrabold text-ink-900">Isi Laporan <span class="text-sm font-medium text-ink-500">(sudah disensor)</span></h2>
                        <p id="d-sanitized" class="mt-3 whitespace-pre-wrap break-words text-ink-800">—</p>
                        <div class="mt-5 rounded-xl bg-ink-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-ink-500">Alasan AI</p>
                            <p id="d-rationale" class="mt-1 text-sm text-ink-700">—</p>
                        </div>
                    </section>

                    <section class="card border border-ink-100 p-6">
                        <h2 class="text-lg font-extrabold text-ink-900">Lampiran</h2>
                        <p class="mt-1 text-sm text-ink-500">Berkas diambil dengan token, bukan tautan publik.</p>
                        <ul id="d-attachments" class="mt-4 space-y-2"></ul>
                        <p id="d-attachments-empty" class="hidden text-sm text-ink-500">Tidak ada lampiran.</p>
                    </section>

                    <section class="card border border-ink-100 p-6">
                        <h2 class="text-lg font-extrabold text-ink-900">Catatan Internal</h2>
                        <p class="mt-1 text-sm text-ink-500">Hanya terlihat oleh Satgas. Tidak dikirim ke pelapor.</p>
                        <ul id="d-notes" class="mt-4 space-y-3"></ul>
                        <form id="note-form" class="mt-4">
                            <label for="note-input" class="label">Tambah catatan</label>
                            <textarea id="note-input" rows="3" maxlength="5000" class="input resize-y" placeholder="Catatan tindak lanjut…"></textarea>
                            <div class="mt-2 flex items-center justify-between">
                                <span id="note-counter" class="text-sm text-ink-500">0 / 5.000</span>
                                <button type="submit" class="btn-primary btn-sm">Simpan Catatan</button>
                            </div>
                            <p id="note-error" class="field-error" role="alert" aria-live="polite"></p>
                        </form>
                    </section>

                    <section class="card border border-ink-100 p-6">
                        <h2 class="text-lg font-extrabold text-ink-900">Pesan ke Pelapor</h2>
                        <p class="mt-1 text-sm text-ink-500">Pesan ini terlihat oleh pelapor pada halaman Cek Tiket.</p>
                        <ul id="d-messages" class="mt-4 space-y-3"></ul>
                        <p id="d-messages-empty" class="hidden text-sm text-ink-500">Belum ada pesan.</p>
                        <form id="message-form" class="mt-4">
                            <label for="message-input" class="label">Tulis pesan</label>
                            <textarea id="message-input" rows="3" maxlength="2000" class="input resize-y" placeholder="Pesan untuk pelapor…"></textarea>
                            <div class="mt-2 flex items-center justify-between">
                                <span id="message-counter" class="text-sm text-ink-500">0 / 2.000</span>
                                <button type="submit" class="btn-primary btn-sm">Kirim Pesan</button>
                            </div>
                            <p id="message-error" class="field-error" role="alert" aria-live="polite"></p>
                        </form>
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="card border border-ink-100 p-6">
                        <h2 class="text-lg font-extrabold text-ink-900">Detail</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="font-semibold text-ink-500">Kategori</dt>
                                <dd id="d-category" class="text-ink-800">—</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-ink-500">Lokasi</dt>
                                <dd id="d-location" class="text-ink-800">—</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-ink-500">Harapan pelapor</dt>
                                <dd id="d-wish" class="text-ink-800">—</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-ink-500">Urgensi AI</dt>
                                <dd id="d-urgency-ai" class="text-ink-800">—</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-ink-500">Dibuat</dt>
                                <dd id="d-created" class="text-ink-800">—</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-ink-500">Kontak</dt>
                                <dd>
                                    <span id="d-contact-locked" class="hidden inline-flex items-center gap-1 rounded-full bg-exit-500/15 px-2.5 py-1 text-xs font-semibold text-ink-700">
                                        Kontak tersedia (terkunci)
                                    </span>
                                    <span id="d-contact-none" class="hidden text-ink-500">Tidak ada kontak</span>
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="card border border-ink-100 p-6">
                        <h2 class="text-lg font-extrabold text-ink-900">Tindakan</h2>

                        <div class="mt-4">
                            <label for="status-select" class="label">Ubah Status</label>
                            <div class="flex gap-2">
                                <select id="status-select" class="input">
                                    <option value="MENUNGGU_TIM">Menunggu Tim</option>
                                    <option value="SEDANG_DIDAMPINGI">Sedang Didampingi</option>
                                    <option value="SELESAI">Selesai</option>
                                </select>
                                <button type="button" id="status-save" class="btn-primary btn-sm shrink-0">Simpan</button>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label for="assign-select" class="label">Tugaskan Petugas</label>
                            <div class="flex gap-2">
                                <select id="assign-select" class="input">
                                    <option value="">Belum ditugaskan</option>
                                </select>
                                <button type="button" id="assign-save" class="btn-primary btn-sm shrink-0">Simpan</button>
                            </div>
                        </div>

                        <button type="button" id="urgency-open" class="btn-outline mt-5 w-full">Ubah Urgensi…</button>

                        <button type="button" id="override-open" data-admin-only class="btn-danger-outline mt-3 hidden w-full">
                            <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path fill="currentColor" d="M10 1 2 4v5c0 4.5 3.2 8.6 8 9.5 4.8-.9 8-5 8-9.5V4l-8-3Zm0 5a3 3 0 0 1 3 3v1h.5a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-7a1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1H7v-1a3 3 0 0 1 3-3Zm0 2a1 1 0 0 0-1 1v1h2v-1a1 1 0 0 0-1-1Z"/></svg>
                            Override Kontak (Admin)
                        </button>
                        <p class="mt-2 hidden text-xs text-alert-700" data-admin-only>Hanya untuk ancaman nyawa. Semua tindakan tercatat di audit log.</p>
                    </section>
                </aside>
            </div>
        </div>
    </div>

    <div id="urgency-modal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-ink-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="urgency-modal-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-pop" data-modal-open="urgency">
            <h2 id="urgency-modal-title" class="text-xl font-extrabold text-ink-900">Ubah Urgensi</h2>
            <p class="mt-1 text-sm text-ink-500">Alasan wajib diisi dan akan tercatat.</p>
            <div class="mt-4">
                <label for="urgency-select" class="label">Urgensi baru</label>
                <select id="urgency-select" class="input">
                    <option value="MERAH">Merah</option>
                    <option value="KUNING">Kuning</option>
                    <option value="HIJAU">Hijau</option>
                </select>
            </div>
            <div class="mt-4">
                <label for="urgency-reason" class="label">Alasan (3–1000 karakter)</label>
                <textarea id="urgency-reason" rows="4" minlength="3" maxlength="1000" class="input resize-y" placeholder="Jelaskan alasan perubahan urgensi…"></textarea>
                <div class="mt-1 flex justify-between text-sm text-ink-500">
                    <span id="urgency-reason-error" class="field-error"></span>
                    <span id="urgency-reason-counter" class="ml-auto">0 / 1.000</span>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" data-modal-close="urgency" class="btn-ghost">Batal</button>
                <button type="button" id="urgency-submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </div>
    </div>

    <div id="override-modal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-ink-900/70 p-4" role="dialog" aria-modal="true" aria-labelledby="override-modal-title">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-pop" data-modal-open="override">
            <div class="relative">
                <h2 id="override-modal-title" class="text-xl font-extrabold text-alert-700">Override Kontak</h2>

                <div data-override-step="1">
                    <div class="mt-3 rounded-xl border-l-4 border-alert-600 bg-alert-50 p-4 text-sm text-alert-700">
                        <strong class="font-bold">Peringatan keras.</strong> Tindakan ini membuka kontak asli pelapor, tercatat permanen di audit log, dan hanya boleh dipakai saat ada ancaman nyawa yang nyata. Penyalahgunaan dapat dikenai sanksi.
                    </div>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600">
                        <li>• Alasan minimal 20 karakter wajib diisi.</li>
                        <li>• PIN Admin diminta dua kali.</li>
                        <li>• Hasil hanya tampil sementara di jendela ini.</li>
                    </ul>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" data-modal-close="override" class="btn-ghost">Batal</button>
                        <button type="button" data-override-next="2" class="btn-danger-outline">Lanjut</button>
                    </div>
                </div>

                <div data-override-step="2" class="hidden">
                    <label for="override-reason" class="label mt-3">Alasan (min. 20 karakter)</label>
                    <textarea id="override-reason" rows="4" maxlength="1000" class="input resize-y" placeholder="Jelaskan mengapa kontak harus dibuka sekarang…"></textarea>
                    <div class="mt-1 flex justify-between text-sm text-ink-500">
                        <span id="override-reason-error" class="field-error"></span>
                        <span id="override-reason-counter">0 / 1.000</span>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" data-override-back="1" class="btn-ghost">Kembali</button>
                        <button type="button" data-override-next="3" class="btn-danger-outline">Lanjut</button>
                    </div>
                </div>

                <div data-override-step="3" class="hidden">
                    <div class="mt-3 space-y-4">
                        <div>
                            <label for="override-pin" class="label">PIN Admin</label>
                            <input id="override-pin" type="password" inputmode="numeric" autocomplete="off" class="input font-mono tracking-widest">
                        </div>
                        <div>
                            <label for="override-pin-confirm" class="label">Konfirmasi PIN</label>
                            <input id="override-pin-confirm" type="password" inputmode="numeric" autocomplete="off" class="input font-mono tracking-widest">
                        </div>
                    </div>
                    <p id="override-error" class="field-error mt-3" role="alert" aria-live="assertive"></p>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" data-override-back="2" class="btn-ghost">Kembali</button>
                        <button type="button" id="override-submit" class="btn-danger-outline">Buka Kontak</button>
                    </div>
                </div>

                <div data-override-step="4" class="hidden">
                    <div class="watermark-overlay" aria-hidden="true"><span>RAHASIA</span></div>
                    <div class="relative z-10">
                        <p class="mt-3 text-sm font-semibold text-ink-600">Kontak</p>
                        <p id="override-contact" class="mt-1 break-words text-lg font-bold text-ink-900">—</p>
                        <p id="override-contact-message" class="mt-2 hidden text-sm text-ink-600"></p>
                        <p class="mt-4 text-sm font-semibold text-ink-600">Teks asli</p>
                        <p id="override-text" class="mt-1 max-h-48 overflow-auto whitespace-pre-wrap break-words rounded-xl bg-ink-50 p-3 text-sm text-ink-800">—</p>
                        <p id="override-countdown" class="mt-4 text-xs font-semibold text-alert-700"></p>
                        <div class="mt-4 flex justify-end">
                            <button type="button" id="override-close-final" class="btn-ghost">Tutup Sekarang</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/laporan.js')
@endpush
