@extends('layouts.officer')

@section('title', 'Pola Terlapor | CampusCare')

@section('content')
@php
    $patterns = $patterns ?? [];
    $items = $patterns['items'] ?? $patterns['groups'] ?? $patterns['patterns'] ?? $patterns;
    $items = is_array($items) && array_is_list($items) ? $items : [];
@endphp

<x-officer-band title="Pola Terlapor" description="Pengelompokan indikasi terlapor yang berulang berdasarkan hash anonim." />

<section class="mx-auto mt-8 max-w-6xl">
    <div class="flex items-start gap-3 rounded-xl border border-brand-200 bg-brand-50 p-4 text-sm text-ink-700">
        <span aria-hidden="true" class="text-lg leading-none text-brand-700">♧</span>
        <p>Hash bukan bukti identitas atau kesalahan seseorang. Hasil pengelompokan memerlukan verifikasi dan penilaian petugas.</p>
    </div>

    <div class="table-shell mt-5">
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
                    <td class="font-mono text-xs text-ink-600">{{ Str::limit($hash, 12, '…') }}</td>
                    <td class="font-semibold text-ink-900">{{ $count }}</td>
                    <td>
                        <span class="flex flex-wrap gap-1.5">
                            @forelse ($codes as $code)
                                <span class="rounded-full bg-ink-100 px-2.5 py-1 font-mono text-xs text-ink-700">{{ $code }}</span>
                            @empty
                                <span class="text-ink-400">—</span>
                            @endforelse
                        </span>
                    </td>
                    <td>
                        @if ($reportId)
                            <a href="{{ route('laporan.detail', $reportId) }}" class="font-semibold text-brand-700 hover:underline">Lihat →</a>
                        @else
                            <span class="text-ink-400">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-10 text-center text-ink-500">Belum ada kelompok pola terlapor.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
