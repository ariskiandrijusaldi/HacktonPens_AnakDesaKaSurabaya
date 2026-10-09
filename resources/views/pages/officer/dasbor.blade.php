@extends('layouts.officer')

@section('title', 'Dasbor Laporan — SATGAS PPKS')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-ink-900">Dasbor Laporan</h1>
            <p class="mt-1 text-ink-600">Urutan dari backend: MERAH lebih dulu, lalu yang paling lama menunggu.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/dasbor/statistik" class="btn-outline btn-sm">Lihat Statistik</a>
        </div>
    </div>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Statistik ringkas">
        <div class="card border border-ink-100 p-5">
            <p class="text-xs font-bold uppercase tracking-wide text-alert-700">Merah · belum selesai</p>
            <p data-stat-merah class="mt-1 text-3xl font-black text-ink-900">—</p>
            <p class="mt-1 text-xs text-ink-500">Target respons &lt; 1 jam</p>
        </div>
        <div class="card border border-ink-100 p-5">
            <p class="text-xs font-bold uppercase tracking-wide text-exit-600">Kuning · belum selesai</p>
            <p data-stat-kuning class="mt-1 text-3xl font-black text-ink-900">—</p>
            <p class="mt-1 text-xs text-ink-500">Target respons &lt; 24 jam</p>
        </div>
        <div class="card border border-ink-100 p-5">
            <p class="text-xs font-bold uppercase tracking-wide text-brand-700">Hijau · belum selesai</p>
            <p data-stat-hijau class="mt-1 text-3xl font-black text-ink-900">—</p>
            <p class="mt-1 text-xs text-ink-500">Dapat dijadwalkan</p>
        </div>
        <div class="card border border-ink-100 p-5">
            <p class="text-xs font-bold uppercase tracking-wide text-ink-500">Total laporan</p>
            <p data-stat-total class="mt-1 text-3xl font-black text-ink-900">—</p>
            <p class="mt-1 text-xs text-ink-500"><span data-stat-aifailed>0</span> triase AI gagal</p>
        </div>
    </section>

    <form id="filter-form" class="mt-6 card border border-ink-100 p-5" aria-label="Filter laporan">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="f-q" class="label">Cari</label>
                <input id="f-q" name="q" type="search" class="input" placeholder="Kode tiket, ringkasan…">
            </div>
            <div>
                <label for="f-status" class="label">Status</label>
                <select id="f-status" name="status" class="input">
                    <option value="">Semua status</option>
                    <option value="MENUNGGU_TIM">Menunggu Tim</option>
                    <option value="SEDANG_DIDAMPINGI">Sedang Didampingi</option>
                    <option value="SELESAI">Selesai</option>
                </select>
            </div>
            <div>
                <label for="f-urgency" class="label">Urgensi</label>
                <select id="f-urgency" name="urgency" class="input">
                    <option value="">Semua urgensi</option>
                    <option value="MERAH">Merah</option>
                    <option value="KUNING">Kuning</option>
                    <option value="HIJAU">Hijau</option>
                </select>
            </div>
            <div>
                <label for="f-category" class="label">Kategori</label>
                <input id="f-category" name="category" type="text" class="input" placeholder="mis. pelecehan verbal">
            </div>
            <div>
                <label for="f-location" class="label">Lokasi</label>
                <input id="f-location" name="location" type="text" class="input" placeholder="mis. Gedung B">
            </div>
            <div>
                <label for="f-assignee" class="label">Ditugaskan ke</label>
                <select id="f-assignee" name="assigned_to" class="input">
                    <option value="">Semua petugas</option>
                </select>
            </div>
            <div>
                <label for="f-limit" class="label">Baris / halaman</label>
                <select id="f-limit" name="limit" class="input">
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">Terapkan</button>
                <button type="button" id="filter-reset" class="btn-ghost">Reset</button>
            </div>
        </div>
    </form>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <p id="list-summary" class="text-sm text-ink-600" role="status" aria-live="polite">Memuat laporan…</p>
        <div class="flex items-center gap-2">
            <button type="button" id="page-prev" class="btn-outline btn-sm">Sebelumnya</button>
            <span id="page-info" class="text-sm text-ink-600">—</span>
            <button type="button" id="page-next" class="btn-outline btn-sm">Berikutnya</button>
        </div>
    </div>

    <div id="list-loading" class="mt-4">
        <x-skeleton :rows="5" />
    </div>

    <div id="list-empty" class="mt-4 hidden rounded-2xl border border-dashed border-ink-300 bg-white p-10 text-center">
        <p class="font-semibold text-ink-700">Tidak ada laporan yang cocok.</p>
        <p class="mt-1 text-sm text-ink-500">Coba ubah filter atau reset pencarian.</p>
    </div>

    <div id="list-shell" class="mt-4 table-shell hidden">
        <table class="data-table">
            <caption class="sr-only">Daftar laporan masuk</caption>
            <thead>
                <tr>
                    <th scope="col">Urgensi</th>
                    <th scope="col">Tiket</th>
                    <th scope="col">Ringkasan</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Lokasi</th>
                    <th scope="col">Status</th>
                    <th scope="col">Menunggu</th>
                    <th scope="col">Petugas</th>
                    <th scope="col"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody id="list-body"></tbody>
        </table>
    </div>

    <template id="row-template">
        <tr data-row>
            <td><span data-cell="urgency"></span></td>
            <td><span data-cell="ticket" class="font-mono text-xs font-semibold text-ink-700"></span></td>
            <td class="max-w-xs">
                <span data-cell="summary" class="line-clamp-2 block text-ink-700"></span>
                <span data-cell="aifailed" class="mt-1 inline-flex hidden items-center gap-1 rounded-full bg-alert-50 px-2 py-0.5 text-[11px] font-bold text-alert-700">Triase AI gagal</span>
            </td>
            <td><span data-cell="category" class="text-xs font-semibold uppercase tracking-wide text-ink-600"></span></td>
            <td><span data-cell="location" class="text-ink-600"></span></td>
            <td><span data-cell="status"></span></td>
            <td><span data-cell="waiting" class="inline-flex items-center gap-1 font-semibold"></span></td>
            <td><span data-cell="assignee" class="text-xs text-ink-600"></span></td>
            <td class="text-right"><a data-cell="link" href="#" class="btn-outline btn-sm whitespace-nowrap">Lihat</a></td>
        </tr>
    </template>
@endsection

@push('scripts')
    @vite('resources/js/pages/dasbor.js')
@endpush
