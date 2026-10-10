@extends('layouts.officer')

@section('title', 'Audit Log | CampusCare')

@section('content')
@php
    $audit = $audit ?? [];
    $filters = $filters ?? [];
    $items = $audit['items'] ?? $audit['logs'] ?? $audit['data'] ?? $audit;
    $items = is_array($items) && array_is_list($items) ? $items : [];
@endphp

<x-officer-band title="Audit Log" description="Riwayat akses dan perubahan penting di sistem." />

<section class="mx-auto mt-8 max-w-6xl">
    <div class="flex items-start gap-3 rounded-xl border border-brand-200 bg-brand-50 p-4 text-sm text-ink-700">
        <span aria-hidden="true" class="text-lg leading-none text-brand-700">♧</span>
        <p>Audit log mencatat akses dan perubahan penting untuk akuntabilitas.</p>
    </div>

    <form method="GET" action="{{ route('audit') }}" class="card mt-5 p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
            <div>
                <label for="auditQ" class="label">Pencarian</label>
                <input id="auditQ" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari detail" class="input">
            </div>
            <div>
                <label for="auditOfficer" class="label">Petugas</label>
                <input id="auditOfficer" name="officer" value="{{ $filters['officer'] ?? '' }}" placeholder="Nama petugas" class="input">
            </div>
            <div>
                <label for="auditFrom" class="label">Dari</label>
                <input id="auditFrom" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="input">
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button type="submit" class="btn-outline btn-sm">Terapkan</button>
        </div>
    </form>

    <div class="table-shell mt-5">
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
                    $detail = $log['detail'] ?? $log['description'] ?? '-';
                    $detail = is_array($detail) ? json_encode($detail, JSON_UNESCAPED_UNICODE) : $detail;
                @endphp
                <tr>
                    <td class="whitespace-nowrap text-ink-600">{{ $log['created_at'] ?? $log['time'] ?? '-' }}</td>
                    <td><span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700">{{ $actionLabel }}</span></td>
                    <td>{{ $log['officer'] ?? $log['officer_id'] ?? '-' }}</td>
                    <td class="font-mono text-xs text-ink-600">{{ $log['report_id'] ?? $log['ticket_code'] ?? '-' }}</td>
                    <td class="text-ink-700">{{ $detail }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-ink-500">Belum ada catatan audit.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
