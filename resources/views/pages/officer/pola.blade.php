@extends('layouts.officer')

@section('title', 'Pola Terlapor — SATGAS PPKS')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-ink-900">Pola Terlapor</h1>
            <p class="mt-1 text-ink-600">Pengelompokan otomatis berdasarkan hash terlapor. Identitas tetap anonim.</p>
        </div>
        <button type="button" id="patterns-refresh" class="btn-outline btn-sm">Muat ulang</button>
    </div>

    <div class="mt-6 rounded-xl border-l-4 border-brand-600 bg-brand-50 p-4 text-sm text-ink-700">
        Label "Terlapor A/B/C" hanyalah penanda lokal untuk halaman ini. Hash tidak dapat dibalik menjadi identitas.
    </div>

    <div id="patterns-loading" class="mt-6">
        <x-skeleton :rows="4" />
    </div>

    <div id="patterns-error" class="mt-6 hidden rounded-2xl border border-alert-200 bg-alert-50 p-6 text-alert-700" role="alert"></div>

    <div id="patterns-empty" class="mt-6 hidden rounded-2xl border border-dashed border-ink-300 bg-white p-10 text-center">
        <p class="font-semibold text-ink-700">Belum ada pola yang terdeteksi.</p>
        <p class="mt-1 text-sm text-ink-500">Pola muncul saat ada beberapa laporan dengan terlapor yang sama.</p>
    </div>

    <div id="patterns-list" class="mt-6 grid gap-5 lg:grid-cols-2"></div>

    <template id="pattern-template">
        <article class="card border border-ink-100 p-6">
            <div class="flex items-center justify-between gap-3">
                <h2 data-pattern-label class="text-lg font-extrabold text-ink-900">Terlapor</h2>
                <span data-pattern-count class="rounded-full bg-alert-600 px-3 py-1 text-sm font-bold text-white">0</span>
            </div>
            <p data-pattern-hash class="mt-1 font-mono text-xs text-ink-400"></p>
            <div class="mt-4">
                <p class="text-xs font-bold uppercase tracking-wide text-ink-500">Kategori</p>
                <div data-pattern-categories class="mt-2 flex flex-wrap gap-2"></div>
            </div>
            <div class="mt-4">
                <p class="text-xs font-bold uppercase tracking-wide text-ink-500">Tiket terkait</p>
                <div data-pattern-tickets class="mt-2 flex flex-wrap gap-2"></div>
            </div>
        </article>
    </template>
@endsection

@push('scripts')
    @vite('resources/js/pages/pola.js')
@endpush
