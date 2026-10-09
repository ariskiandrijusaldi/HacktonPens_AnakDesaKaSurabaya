@extends('layouts.app')

@section('title', 'Pola Terlapor | CampusCare')

@section('content')
@php
    $patterns = $patterns ?? [];
    $items = $patterns['items'] ?? $patterns['groups'] ?? $patterns['data'] ?? $patterns;
    $items = array_is_list($items) ? $items : [];
@endphp
<div class="officer-layout">
    <x-officer-sidebar active="pola" />

    <section class="officer-main">
        <header class="dashboard-header">
            <div><h1>Pola Terlapor</h1><p>Pengelompokan laporan berdasarkan hash anonim.</p></div>
        </header>

        <div class="dashboard-content">
            <div class="privacy-notice">
                <span aria-hidden="true">♧</span>
                <p>Hash bukan bukti identitas atau kesalahan seseorang. Hasil pengelompokan memerlukan verifikasi dan penilaian petugas.</p>
            </div>

            <div class="panel table-panel">
                <div class="table-scroll">
                    <table class="data-table" aria-label="Kelompok pola terlapor">
                        <thead>
                            <tr><th>Hash</th><th>Jumlah</th><th>Kode Tiket</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                        @forelse ($items as $group)
                            @php
                                $count = $group['count'] ?? count($group['ticket_codes'] ?? []);
                                $codes = $group['ticket_codes'] ?? [];
                                $reportId = $group['report_id'] ?? $group['id'] ?? null;
                                $hash = $group['perpetrator_hash'] ?? $group['hash'] ?? null;
                            @endphp
                            <tr>
                                <td><code>{{ Str::limit($hash, 12, '…') }}</code></td>
                                <td>{{ $count }}</td>
                                <td>
                                    @forelse ($codes as $code)
                                        <span class="demo-badge">{{ $code }}</span>
                                    @empty
                                        <span class="field-hint">—</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if ($reportId)
                                        <a href="{{ route('laporan.detail', $reportId) }}" class="table-action">Lihat →</a>
                                    @else
                                        <span class="field-hint">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state" style="text-align:center;padding:30px">
                                    Belum ada kelompok pola terlapor.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection