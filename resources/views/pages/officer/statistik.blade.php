@extends('layouts.officer')

@section('title', 'Statistik — SATGAS PPKS')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-ink-900">Statistik</h1>
            <p class="mt-1 text-ink-600">Gambaran singkat beban dan kecepatan respons Satgas.</p>
        </div>
        <button type="button" id="stats-refresh" class="btn-outline btn-sm">Muat ulang</button>
    </div>

    <div id="stats-loading" class="mt-6">
        <x-skeleton :rows="3" />
    </div>

    <div id="stats-error" class="mt-6 hidden rounded-2xl border border-alert-200 bg-alert-50 p-6 text-alert-700" role="alert"></div>

    <div id="stats-content" class="mt-6 hidden">
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Ringkasan angka">
            <div class="card border border-ink-100 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-ink-500">Total laporan</p>
                <p data-stat="total" class="mt-1 text-3xl font-black text-ink-900">—</p>
            </div>
            <div class="card border border-ink-100 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-ink-500">Rata-rata respons pertama</p>
                <p data-stat="response" class="mt-1 text-3xl font-black text-ink-900">—</p>
                <p class="mt-1 text-xs text-ink-500">dalam jam</p>
            </div>
            <div class="card border border-ink-100 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-ink-500">Triase AI gagal</p>
                <p data-stat="aifailed" class="mt-1 text-3xl font-black text-alert-700">—</p>
            </div>
            <div class="card border border-ink-100 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-ink-500">Status selesai</p>
                <p data-stat="done" class="mt-1 text-3xl font-black text-brand-700">—</p>
            </div>
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="card border border-ink-100 p-6">
                <h2 class="text-lg font-extrabold text-ink-900">Distribusi Urgensi</h2>
                <div class="mt-4 flex flex-col items-center gap-6 sm:flex-row">
                    <svg data-urgency-donut viewBox="0 0 42 42" class="h-44 w-44 -rotate-90" role="img" aria-label="Diagram donat distribusi urgensi">
                        <circle cx="21" cy="21" r="15.9" fill="none" stroke="#e5e7eb" stroke-width="6"></circle>
                    </svg>
                    <ul data-urgency-legend class="flex-1 space-y-2 text-sm"></ul>
                </div>
            </section>

            <section class="card border border-ink-100 p-6">
                <h2 class="text-lg font-extrabold text-ink-900">Distribusi Status</h2>
                <ul data-status-bars class="mt-4 space-y-4"></ul>
            </section>
        </div>

        <section class="mt-6 card border border-ink-100 p-6">
            <h2 class="text-lg font-extrabold text-ink-900">Kategori Terbanyak</h2>
            <p class="mt-1 text-sm text-ink-500">Sepuluh kategori dengan jumlah laporan terbanyak.</p>
            <ul data-category-bars class="mt-4 space-y-4"></ul>
        </section>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/statistik.js')
@endpush
