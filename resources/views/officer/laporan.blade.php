@extends('layouts.officer')

@section('title', 'Laporan Masuk | CampusCare')

@section('content')
@php
    $reports = $reports ?? [];
    $stats = $stats ?? [];
    $items = $reports['items'] ?? [];
    $page = (int) ($reports['page'] ?? 1);
    $totalFiltered = (int) ($reports['total'] ?? 0);
    $limit = (int) ($reports['limit'] ?? 20);
    $lastPage = max(1, (int) ceil($totalFiltered / $limit));

    $q = trim((string) request()->query('q', ''));
    $statusFilter = request()->query('status');
    $urgencyFilter = request()->query('urgency');

    $byStatus = [];
    foreach (($stats['by_status'] ?? []) as $row) {
        $byStatus[$row['key']] = (int) $row['count'];
    }
    $totalAll = (int) ($stats['total_reports'] ?? $totalFiltered);
    $aiFailed = (int) ($stats['ai_failed_count'] ?? 0);

    $link = function (array $overrides) use ($q, $statusFilter, $urgencyFilter) {
        return route('laporan', array_filter(array_merge([
            'q' => $q !== '' ? $q : null,
            'status' => $statusFilter,
            'urgency' => $urgencyFilter,
        ], $overrides)));
    };

    $statusTabs = [
        ['key' => '', 'label' => 'Semua', 'active' => $statusFilter === null || $statusFilter === ''],
        ['key' => 'MENUNGGU_TIM', 'label' => 'Menunggu Tim', 'active' => $statusFilter === 'MENUNGGU_TIM'],
        ['key' => 'SEDANG_DIDAMPINGI', 'label' => 'Didampingi', 'active' => $statusFilter === 'SEDANG_DIDAMPINGI'],
        ['key' => 'SELESAI', 'label' => 'Selesai', 'active' => $statusFilter === 'SELESAI'],
    ];
    $urgencyTabs = [
        ['key' => '', 'label' => 'Semua', 'active' => $urgencyFilter === null || $urgencyFilter === ''],
        ['key' => 'MERAH', 'label' => 'Merah', 'active' => $urgencyFilter === 'MERAH'],
        ['key' => 'KUNING', 'label' => 'Kuning', 'active' => $urgencyFilter === 'KUNING'],
        ['key' => 'HIJAU', 'label' => 'Hijau', 'active' => $urgencyFilter === 'HIJAU'],
    ];

    $statusCard = [
        'MENUNGGU_TIM' => ['label' => 'Menunggu Tim', 'class' => 'bg-amber-100 text-amber-800'],
        'SEDANG_DIDAMPINGI' => ['label' => 'Didampingi', 'class' => 'bg-blue-100 text-blue-700'],
        'SELESAI' => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-700'],
    ];
    $urgencyAccent = [
        'MERAH' => 'border-red-400',
        'KUNING' => 'border-yellow-400',
        'HIJAU' => 'border-green-400',
    ];
@endphp

@php
    $subtitle = $totalFiltered > 0
        ? 'Menampilkan '.$totalFiltered.' laporan.'
        : 'Belum ada laporan yang cocok.';
    if ($aiFailed > 0) {
        $subtitle .= ' AI memerlukan tinjauan manual pada '.$aiFailed.' laporan.';
    }
@endphp

<x-officer-band title="Laporan Masuk" :description="$subtitle" />

<section class="mx-auto mt-8 max-w-6xl">
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <a href="{{ $link(['status' => null]) }}" class="card flex items-center gap-3 p-4 transition-transform duration-150 hover:-translate-y-0.5">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-ink-900 text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </span>
            <span class="min-w-0">
                <span class="block text-2xl font-extrabold leading-none text-ink-900">{{ $totalAll }}</span>
                <span class="mt-1 block text-xs font-semibold uppercase tracking-wide text-ink-400">Total Laporan</span>
            </span>
        </a>

        <a href="{{ $link(['status' => 'MENUNGGU_TIM']) }}" class="card flex items-center gap-3 p-4 transition-transform duration-150 hover:-translate-y-0.5">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </span>
            <span class="min-w-0">
                <span class="block text-2xl font-extrabold leading-none text-ink-900">{{ $byStatus['MENUNGGU_TIM'] ?? 0 }}</span>
                <span class="mt-1 block text-xs font-semibold uppercase tracking-wide text-ink-400">Menunggu Tim</span>
            </span>
        </a>

        <a href="{{ $link(['status' => 'SEDANG_DIDAMPINGI']) }}" class="card flex items-center gap-3 p-4 transition-transform duration-150 hover:-translate-y-0.5">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </span>
            <span class="min-w-0">
                <span class="block text-2xl font-extrabold leading-none text-ink-900">{{ $byStatus['SEDANG_DIDAMPINGI'] ?? 0 }}</span>
                <span class="mt-1 block text-xs font-semibold uppercase tracking-wide text-ink-400">Didampingi</span>
            </span>
        </a>

        <a href="{{ $link(['status' => 'SELESAI']) }}" class="card flex items-center gap-3 p-4 transition-transform duration-150 hover:-translate-y-0.5">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-green-100 text-green-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </span>
            <span class="min-w-0">
                <span class="block text-2xl font-extrabold leading-none text-ink-900">{{ $byStatus['SELESAI'] ?? 0 }}</span>
                <span class="mt-1 block text-xs font-semibold uppercase tracking-wide text-ink-400">Selesai</span>
            </span>
        </a>
    </div>

    <div class="card mt-5 p-4">
        <form method="GET" action="{{ route('laporan') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex max-w-md flex-1 gap-2">
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari isi laporan…"
                       class="input">
                @if ($statusFilter)
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                @if ($urgencyFilter)
                    <input type="hidden" name="urgency" value="{{ $urgencyFilter }}">
                @endif
                <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            </div>

            <div class="flex flex-col gap-2 text-sm sm:flex-row sm:items-center sm:gap-4">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-xs font-bold uppercase tracking-wide text-ink-400">Status</span>
                    @foreach ($statusTabs as $tab)
                        <a href="{{ $link(['status' => $tab['key'] === '' ? null : $tab['key']]) }}"
                           class="rounded-full px-3 py-1 text-sm font-semibold transition-colors {{ $tab['active'] ? 'bg-ink-900 text-white' : 'bg-ink-100 text-ink-600 hover:bg-ink-200' }}">
                            {{ $tab['label'] }}
                        </a>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-xs font-bold uppercase tracking-wide text-ink-400">Urgensi</span>
                    @foreach ($urgencyTabs as $tab)
                        <a href="{{ $link(['urgency' => $tab['key'] === '' ? null : $tab['key']]) }}"
                           class="rounded-full px-3 py-1 text-sm font-semibold transition-colors {{ $tab['active'] ? 'bg-ink-900 text-white' : 'bg-ink-100 text-ink-600 hover:bg-ink-200' }}">
                            {{ $tab['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    @if (count($items) === 0)
        <div class="card mt-5 p-8 text-center">
            <p class="text-lg font-bold text-ink-900">Belum ada laporan</p>
            <p class="mt-1 text-sm text-ink-500">
                Tidak ada laporan yang cocok dengan filter saat ini.
                <a href="{{ route('laporan') }}" class="font-semibold text-brand-700 hover:underline">Tampilkan semua laporan</a>.
            </p>
        </div>
    @else
        <ul class="mt-5 grid grid-cols-1 gap-4">
            @foreach ($items as $report)
                @php
                    $urgency = $report['urgency_final'] ?? $report['urgency_ai'] ?? null;
                    $status = $report['status'] ?? null;
                    $createdAt = $report['created_at'] ?? null;
                    $waiting = (float) ($report['waiting_hours'] ?? 0);
                    $aiFailedItem = (bool) ($report['ai_failed'] ?? false);
                    $accent = $urgencyAccent[$urgency] ?? 'border-ink-200';
                    $statusStyle = $statusCard[$status] ?? ['label' => $status ?: 'Tidak diketahui', 'class' => 'bg-ink-100 text-ink-600'];
                    $createdLabel = $createdAt
                        ? \Carbon\Carbon::parse($createdAt)->format('d M Y, H:i')
                        : '—';
                    $category = ucfirst((string) ($report['category'] ?? 'Lainnya'));
                @endphp
                <li>
                    <a href="{{ route('laporan.detail', $report['id']) }}" class="card card-hover group border-l-4 {{ $accent }}">
                        <div class="card-content">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-semibold text-ink-400">{{ $report['ticket_code'] }}</span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusStyle['class'] }}">{{ $statusStyle['label'] }}</span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $urgency === 'MERAH' ? 'bg-red-100 text-red-700' : ($urgency === 'KUNING' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">{{ $urgency ?: 'N/A' }}</span>
                                @if ($aiFailedItem)
                                    <span class="rounded-full bg-alert-100 px-2.5 py-0.5 text-xs font-semibold text-alert-700" title="Perlu tinjauan manual">⚠ Tinjau manual</span>
                                @endif
                            </div>

                            <h3 class="card-title mt-2 line-clamp-2">{{ $report['summary'] }}</h3>

                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-ink-500">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-4 w-4 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    {{ $category }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-4 w-4 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $report['location'] }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-4 w-4 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $createdLabel }}
                                </span>
                                @if ($status === 'MENUNGGU_TIM' && $waiting > 0)
                                    <span class="inline-flex items-center gap-1 font-medium text-amber-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ number_format($waiting, 1) }} jam menunggu
                                    </span>
                                @endif
                            </div>

                            @if (! empty($report['assigned_to']))
                                <span class="mt-2 inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wide text-ink-400">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Sudah ditugaskan
                                </span>
                            @endif
                        </div>

                        <span class="shrink-0 self-center rounded-full bg-ink-100 px-3 py-1.5 text-sm font-semibold text-ink-700 transition-colors group-hover:bg-brand-100 group-hover:text-brand-700">Lihat →</span>
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($lastPage > 1)
            <nav class="mt-6 flex items-center justify-between" aria-label="Navigasi halaman">
                @if ($page > 1)
                    <a href="{{ $link(['page' => $page - 1]) }}"
                       class="btn btn-outline btn-sm">← Sebelumnya</a>
                @else
                    <span></span>
                @endif

                <span class="text-sm text-ink-500">Halaman {{ $page }} dari {{ $lastPage }}</span>

                @if ($page < $lastPage)
                    <a href="{{ $link(['page' => $page + 1]) }}"
                       class="btn btn-outline btn-sm">Berikutnya →</a>
                @else
                    <span></span>
                @endif
            </nav>
        @endif
    @endif
</section>
@endsection