@extends('layouts.officer')

@section('title', 'Audit Log — SATGAS PPKS')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-ink-900">Audit Log</h1>
            <p class="mt-1 text-ink-600">Khusus Admin. Mencatat tindakan sensitif, termasuk setiap pembukaan kontak.</p>
        </div>
        <button type="button" id="audit-refresh" class="btn-outline btn-sm">Muat ulang</button>
    </div>

    <form id="audit-filter" class="mt-6 card border border-ink-100 p-5" aria-label="Filter audit">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="audit-action" class="label">Aksi</label>
                <select id="audit-action" class="input">
                    <option value="">Semua aksi</option>
                </select>
            </div>
            <div>
                <label for="audit-from" class="label">Dari tanggal</label>
                <input id="audit-from" type="date" class="input">
            </div>
            <div>
                <label for="audit-to" class="label">Sampai tanggal</label>
                <input id="audit-to" type="date" class="input">
            </div>
            <div class="flex items-end">
                <button type="button" id="audit-reset" class="btn-ghost w-full">Reset</button>
            </div>
        </div>
    </form>

    <div id="audit-loading" class="mt-6">
        <x-skeleton :rows="5" />
    </div>

    <div id="audit-error" class="mt-6 hidden rounded-2xl border border-alert-200 bg-alert-50 p-6 text-alert-700" role="alert"></div>

    <div id="audit-empty" class="mt-6 hidden rounded-2xl border border-dashed border-ink-300 bg-white p-10 text-center">
        <p class="font-semibold text-ink-700">Tidak ada catatan audit yang cocok.</p>
    </div>

    <div id="audit-shell" class="mt-6 table-shell hidden">
        <table class="data-table">
            <caption class="sr-only">Catatan audit log</caption>
            <thead>
                <tr>
                    <th scope="col">Waktu</th>
                    <th scope="col">Aksi</th>
                    <th scope="col">Petugas</th>
                    <th scope="col">Laporan</th>
                    <th scope="col">Detail</th>
                </tr>
            </thead>
            <tbody id="audit-body"></tbody>
        </table>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/audit.js')
@endpush
