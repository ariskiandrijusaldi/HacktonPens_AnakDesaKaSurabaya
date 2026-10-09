@extends('layouts.app')

@section('title', 'Audit Log | CampusCare')

@section('content')
@php
    $audit = $audit ?? [];
    $filters = $filters ?? [];
    $items = $audit['items'] ?? $audit['logs'] ?? $audit['data'] ?? $audit;
    $items = array_is_list($items) ? $items : [];
@endphp
<div class="officer-layout">
    <x-officer-sidebar active="audit" />

    <section class="officer-main">
        <header class="dashboard-header">
            <div><h1>Audit Log</h1><p>Riwayat akses dan perubahan penting di sistem.</p></div>
        </header>

        <div class="dashboard-content">
            <div class="privacy-notice">
                <span aria-hidden="true">♧</span>
                <p>Audit log mencatat akses dan perubahan penting untuk akuntabilitas.</p>
            </div>

            <form method="GET" action="{{ route('audit') }}" class="panel filter-panel">
                <div class="panel-heading">
                    <div><h2>Filter Riwayat</h2></div>
                    <button type="submit" class="btn btn-outline">Terapkan</button>
                </div>
                <div class="filter-grid">
                    <div class="field-group">
                        <label for="auditQ">Pencarian</label>
                        <input id="auditQ" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari detail">
                    </div>
                    <div class="field-group">
                        <label for="auditOfficer">Petugas</label>
                        <input id="auditOfficer" name="officer" value="{{ $filters['officer'] ?? '' }}" placeholder="Nama petugas">
                    </div>
                    <div class="field-group">
                        <label for="auditFrom">Dari</label>
                        <input id="auditFrom" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                    </div>
                </div>
            </form>

            <div class="panel table-panel">
                <div class="table-scroll">
                    <table class="data-table" aria-label="Riwayat audit log">
                        <thead>
                            <tr><th>Waktu</th><th>Aksi</th><th>Petugas</th><th>Laporan</th><th>Detail</th></tr>
                        </thead>
                        <tbody>
                        @forelse ($items as $log)
                            @php
                                $action = $log['action'] ?? $log['event'] ?? null;
                                $actionLabel = match (strtoupper((string) $action)) {
                                    'STATUS', 'UBAH_STATUS', 'UPDATE_STATUS' => 'Ubah status',
                                    'ASSIGN', 'PENUGASAN' => 'Penugasan',
                                    'URGENCY', 'URGENSI' => 'Ubah urgensi',
                                    'NOTE', 'CATATAN' => 'Catatan',
                                    'MESSAGE', 'PESAN' => 'Pesan',
                                    'OVERRIDE' => 'Override',
                                    'LOGIN' => 'Login',
                                    default => $action ?: 'Aksi',
                                };
                            @endphp
                            <tr>
                                <td>{{ $log['created_at'] ?? $log['time'] ?? '-' }}</td>
                                <td><span class="status-badge status-progress">{{ $actionLabel }}</span></td>
                                <td>{{ $log['officer'] ?? '-' }}</td>
                                <td>{{ $log['report_id'] ?? $log['ticket_code'] ?? '-' }}</td>
                                <td>{{ $log['detail'] ?? $log['description'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state" style="text-align:center;padding:30px">
                                    Belum ada catatan audit.
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