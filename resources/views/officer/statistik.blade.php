@extends('layouts.officer')

@section('title', 'Statistik Laporan | CampusCare')

@section('content')
@php
    $stats = $stats ?? [];
    $total = (int) ($stats['total_reports'] ?? $stats['total'] ?? 0);
    $byStatus = $stats['by_status'] ?? [];
    $byUrgency = $stats['by_urgency'] ?? [];
    $byCategory = $stats['by_category'] ?? [];

    $statusMap = [
        'MENUNGGU_TIM' => 'Menunggu',
        'SEDANG_DIDAMPINGI' => 'Diproses',
        'SELESAI' => 'Selesai',
    ];
    $statusFill = ['MENUNGGU_TIM' => 'bg-amber-500', 'SEDANG_DIDAMPINGI' => 'bg-sky-500', 'SELESAI' => 'bg-emerald-500'];
    $urgencyFill = ['MERAH' => 'bg-red-500', 'KUNING' => 'bg-amber-500', 'HIJAU' => 'bg-emerald-500'];

    $counts = function (array $rows) {
        $out = [];
        foreach ($rows as $row) {
            $key = $row['key'] ?? $row['category'] ?? null;
            $n = (int) ($row['count'] ?? $row['total'] ?? 0);
            if ($key !== null) { $out[$key] = $n; }
        }
        return $out;
    };
    $statusCounts = $counts(is_array($byStatus) ? $byStatus : []);
    $urgencyCounts = $counts(is_array($byUrgency) ? $byUrgency : []);
    $categoryCounts = $counts(is_array($byCategory) ? $byCategory : []);
    $statusTotal = array_sum($statusCounts) ?: max($total, 1);
    $urgencyTotal = array_sum($urgencyCounts) ?: max($total, 1);
    $categoryTotal = array_sum($categoryCounts) ?: max($total, 1);
    $doneCount = $statusCounts['SELESAI'] ?? 0;
    $donePct = $statusTotal > 0 ? round($doneCount / $statusTotal * 100, 1) : 0;
@endphp

<x-officer-band title="Statistik Laporan" description="Distribusi laporan berdasarkan status, urgensi, dan kategori." />

<section class="mx-auto mt-8 max-w-6xl">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <article class="card p-6">
            <span class="text-xs font-semibold uppercase tracking-wide text-ink-400">Total laporan</span>
            <strong class="mt-2 block text-4xl font-extrabold text-ink-900">{{ $total }}</strong>
            <small class="text-ink-500">Semua kategori</small>
        </article>
        <article class="card p-6">
            <span class="text-xs font-semibold uppercase tracking-wide text-ink-400">Selesai</span>
            <strong class="mt-2 block text-4xl font-extrabold text-ink-900">{{ $doneCount }}</strong>
            <small class="text-ink-500">{{ $donePct }}% dari total</small>
        </article>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
        <section class="card p-6">
            <h2 class="text-lg font-bold text-ink-900">Distribusi Status</h2>
            @if (count($statusCounts))
                <div class="mt-4 grid gap-4">
                    @foreach ($statusMap as $key => $label)
                        @if (isset($statusCounts[$key]))
                            @php
                                $n = $statusCounts[$key];
                                $pct = $statusTotal > 0 ? round($n / $statusTotal * 100, 1) : 0;
                            @endphp
                            <div class="grid grid-cols-[minmax(80px,1fr)_2fr_32px] items-center gap-3 text-sm">
                                <span class="text-ink-600">{{ $label }}</span>
                                <span class="block h-2.5 overflow-hidden rounded-full bg-ink-100">
                                    <span class="block h-full rounded-full {{ $statusFill[$key] }}" style="width: {{ $pct }}%"></span>
                                </span>
                                <strong class="text-right text-ink-900">{{ $n }}</strong>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-sm text-ink-500">Belum ada data status.</p>
            @endif
        </section>

        <section class="card p-6">
            <h2 class="text-lg font-bold text-ink-900">Distribusi Urgensi</h2>
            @if (count($urgencyCounts))
                <div class="mt-4 grid gap-4">
                    @foreach (['MERAH', 'KUNING', 'HIJAU'] as $key)
                        @if (isset($urgencyCounts[$key]))
                            @php
                                $n = $urgencyCounts[$key];
                                $pct = $urgencyTotal > 0 ? round($n / $urgencyTotal * 100, 1) : 0;
                            @endphp
                            <div class="grid grid-cols-[minmax(80px,1fr)_2fr_32px] items-center gap-3 text-sm">
                                <span class="text-ink-600">{{ ucfirst(strtolower($key)) }}</span>
                                <span class="block h-2.5 overflow-hidden rounded-full bg-ink-100">
                                    <span class="block h-full rounded-full {{ $urgencyFill[$key] }}" style="width: {{ $pct }}%"></span>
                                </span>
                                <strong class="text-right text-ink-900">{{ $n }}</strong>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-sm text-ink-500">Belum ada data urgensi.</p>
            @endif
        </section>

        @if (count($categoryCounts))
            <section class="card p-6 lg:col-span-2">
                <h2 class="text-lg font-bold text-ink-900">Distribusi Kategori</h2>
                <div class="mt-4 grid gap-4">
                    @foreach ($categoryCounts as $category => $n)
                        @php $pct = $categoryTotal > 0 ? round($n / $categoryTotal * 100, 1) : 0; @endphp
                        <div class="grid grid-cols-[minmax(80px,1fr)_2fr_32px] items-center gap-3 text-sm">
                            <span class="text-ink-600">{{ $category }}</span>
                            <span class="block h-2.5 overflow-hidden rounded-full bg-ink-100">
                                <span class="block h-full rounded-full bg-emerald-500" style="width: {{ $pct }}%"></span>
                            </span>
                            <strong class="text-right text-ink-900">{{ $n }}</strong>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</section>
@endsection
