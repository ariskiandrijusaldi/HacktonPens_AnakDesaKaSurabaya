@extends('layouts.app')

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
    $statusFill = ['MENUNGGU_TIM' => 'yellow-fill', 'SEDANG_DIDAMPINGI' => 'blue-fill', 'SELESAI' => 'green-fill'];
    $urgencyFill = ['MERAH' => 'red-fill', 'KUNING' => 'yellow-fill', 'HIJAU' => 'green-fill'];

    $counts = function (array $rows) use ($total) {
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
<div class="officer-layout">
    <x-officer-sidebar active="statistik" />

    <section class="officer-main">
        <header class="dashboard-header">
            <div><h1>Statistik Laporan</h1><p>Distribusi laporan berdasarkan status, urgensi, dan kategori.</p></div>
        </header>

        <div class="dashboard-content">
            <div class="stat-grid">
                <article class="stat-card">
                    <span>Total Laporan</span><strong>{{ $total }}</strong><small>Semua kategori</small>
                </article>
                <article class="stat-card">
                    <span>Selesai</span><strong>{{ $doneCount }}</strong><small>{{ $donePct }}% dari total</small>
                </article>
            </div>

            <div class="chart-grid">
                <section class="panel">
                    <h2>Distribusi Status</h2>
                    @php $rendered = false; @endphp
                    @foreach ($statusMap as $key => $label)
                        @if (isset($statusCounts[$key]))
                            @php
                                $rendered = true;
                                $n = $statusCounts[$key];
                                $pct = $statusTotal > 0 ? round($n / $statusTotal * 100, 1) : 0;
                            @endphp
                            <div class="bar-row">
                                <span>{{ $label }}</span>
                                <div class="bar-track"><div class="bar-fill {{ $statusFill[$key] }}" style="width:{{ $pct }}%"></div></div>
                                <strong>{{ $n }}</strong>
                            </div>
                        @endif
                    @endforeach
                    @unless ($rendered)
                        <p class="empty-state">Belum ada data status.</p>
                    @endunless
                </section>

                <section class="panel">
                    <h2>Distribusi Urgensi</h2>
                    @foreach (['MERAH', 'KUNING', 'HIJAU'] as $key)
                        @if (isset($urgencyCounts[$key]))
                            @php
                                $rendered = true;
                                $n = $urgencyCounts[$key];
                                $pct = $urgencyTotal > 0 ? round($n / $urgencyTotal * 100, 1) : 0;
                            @endphp
                            <div class="bar-row">
                                <span>{{ ucfirst(strtolower($key)) }}</span>
                                <div class="bar-track"><div class="bar-fill {{ $urgencyFill[$key] }}" style="width:{{ $pct }}%"></div></div>
                                <strong>{{ $n }}</strong>
                            </div>
                        @endif
                    @endforeach
                </section>

                @if (count($categoryCounts))
                    <section class="panel">
                        <h2>Distribusi Kategori</h2>
                        @foreach ($categoryCounts as $category => $n)
                            @php $pct = $categoryTotal > 0 ? round($n / $categoryTotal * 100, 1) : 0; @endphp
                            <div class="bar-row">
                                <span>{{ $category }}</span>
                                <div class="bar-track"><div class="bar-fill green-fill" style="width:{{ $pct }}%"></div></div>
                                <strong>{{ $n }}</strong>
                            </div>
                        @endforeach
                    </section>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection