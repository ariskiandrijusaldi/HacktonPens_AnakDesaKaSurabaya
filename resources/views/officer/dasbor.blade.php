@extends('layouts.app')

@section('title', 'Dasbor Petugas | CampusCare')

@section('content')
@php
    $filters = $filters ?? [];
    $reports = $reports ?? [];
    $items = $reports['items'] ?? $reports['reports'] ?? $reports['data'] ?? $reports;
    $items = array_is_list($items) ? $items : [];
    $total = $reports['total'] ?? count($items);
    $page = $reports['page'] ?? (int) ($filters['page'] ?? 1);
    $perPage = $reports['per_page'] ?? max(1, count($items));
    $lastPage = (int) ceil($total / max(1, $perPage));
    if ($lastPage < 1) { $lastPage = 1; }
@endphp
<div class="officer-layout">
    <x-officer-sidebar active="dasbor" />

    <section class="officer-main">
        <header class="dashboard-header">
            <button type="button" class="mobile-menu" aria-label="Buka menu">☰</button>
            <div>
                <h1>Dasbor Laporan</h1>
                <p>Ringkasan laporan dan tindak lanjut Satgas.</p>
            </div>
        </header>

        <div class="dashboard-content">
            @if ($errors->any())
                <div class="form-error" role="alert">
                    @foreach ($errors->all() as $error){{ $error }} @endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('dasbor') }}" class="panel filter-panel">
                <div class="panel-heading">
                    <div><h2>Daftar Laporan</h2><p>Pantau laporan dan tingkat urgensi.</p></div>
                    <button type="submit" class="btn btn-outline">↻ Terapkan Filter</button>
                </div>

                <div class="filter-grid">
                    <div class="field-group">
                        <label for="filterQ">Pencarian</label>
                        <input id="filterQ" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari kategori atau lokasi">
                    </div>
                    <div class="field-group">
                        <label for="filterStatus">Status</label>
                        <select id="filterStatus" name="status">
                            <option value="">Semua status</option>
                            @foreach (['MENUNGGU_TIM', 'SEDANG_DIDAMPINGI', 'SELESAI'] as $s)
                                <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>
                                    {{ match ($s) {
                                        'MENUNGGU_TIM' => 'Menunggu',
                                        'SEDANG_DIDAMPINGI' => 'Diproses',
                                        'SELESAI' => 'Selesai',
                                        default => $s,
                                    } }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="filterUrgency">Urgensi</label>
                        <select id="filterUrgency" name="urgency">
                            <option value="">Semua urgensi</option>
                            @foreach (['MERAH', 'KUNING', 'HIJAU'] as $u)
                                <option value="{{ $u }}" @selected(($filters['urgency'] ?? '') === $u)>{{ $u }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="filterLocation">Lokasi</label>
                        <input id="filterLocation" name="location" value="{{ $filters['location'] ?? '' }}" placeholder="Filter lokasi">
                    </div>
                    <div class="field-group">
                        <label for="filterAssigned">Petugas</label>
                        <input id="filterAssigned" name="assigned_to" value="{{ $filters['assigned_to'] ?? '' }}" placeholder="Nama petugas">
                    </div>
                    <div class="field-group">
                        <label for="filterCategory">Kategori</label>
                        <input id="filterCategory" name="category" value="{{ $filters['category'] ?? '' }}" placeholder="Filter kategori">
                    </div>
                </div>
            </form>

            <div class="panel table-panel">
                <div class="table-scroll">
                    <table class="data-table" aria-label="Daftar laporan">
                        <thead>
                            <tr>
                                <th>Kode Tiket</th>
                                <th>Kategori</th>
                                <th>Urgensi</th>
                                <th>Status</th>
                                <th>Lokasi</th>
                                <th>Petugas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($items as $report)
                            @php
                                $id = $report['id'] ?? null;
                                $ticket = $report['ticket_code'] ?? '—';
                                $urgency = $report['urgency_final'] ?? $report['urgency_ai'] ?? null;
                                $status = $report['status'] ?? null;
                                $urgencyClass = match ($urgency) {
                                    'MERAH' => 'urgency-red',
                                    'KUNING' => 'urgency-yellow',
                                    'HIJAU' => 'urgency-green',
                                    default => 'status-waiting',
                                };
                                $statusClass = match ($status) {
                                    'SELESAI', 'DONE' => 'status-done',
                                    'SEDANG_DIDAMPINGI', 'PROSES' => 'status-progress',
                                    default => 'status-waiting',
                                };
                                $statusLabel = match ($status) {
                                    'SELESAI', 'DONE' => 'Selesai',
                                    'SEDANG_DIDAMPINGI', 'PROSES' => 'Diproses',
                                    'MENUNGGU_TIM', 'WAITING' => 'Menunggu',
                                    default => $status ?: 'Tidak diketahui',
                                };
                                $category = $report['category'] ?? 'Lainnya';
                                $location = $report['location'] ?? '—';
                                $assignee = $report['assigned_to'] ?? 'Belum ditugaskan';
                            @endphp
                            <tr>
                                <td><strong>{{ $ticket }}</strong></td>
                                <td>{{ $category }}</td>
                                <td><span class="urgency {{ $urgencyClass }}">{{ $urgency ?: 'N/A' }}</span></td>
                                <td><span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td>{{ $location }}</td>
                                <td>{{ $assignee }}</td>
                                <td>
                                    @if ($id)
                                        <a href="{{ route('laporan.detail', $id) }}" class="table-action">Detail →</a>
                                    @else
                                        <span class="field-hint">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state" style="text-align:center;padding:30px">
                                    Belum ada laporan yang cocok dengan filter.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($lastPage > 1)
                    <div class="table-footer">
                        <span>Menampilkan {{ min($total, $perPage) }} dari {{ $total }} laporan</span>
                        <div>
                            <a href="{{ route('dasbor', array_merge($filters, ['page' => max(1, $page - 1)])) }}"
                               class="btn btn-outline @if ($page <= 1) disabled @endif">← Sebelumnya</a>
                            <a href="{{ route('dasbor', array_merge($filters, ['page' => min($lastPage, $page + 1)])) }}"
                               class="btn btn-outline @if ($page >= $lastPage) disabled @endif">Selanjutnya →</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection