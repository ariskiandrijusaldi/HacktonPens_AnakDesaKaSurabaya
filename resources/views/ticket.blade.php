@extends('layouts.app')

@section('title', 'Cek Status Laporan | CampusCare')

@section('content')
<div class="container page-space narrow">
    <div class="section-title">
        <span class="eyebrow">PELACAKAN LAPORAN</span>
        <h1>Cek Status Laporan</h1>
        <p>Masukkan kode tiket yang kamu terima untuk melihat status dan pesan dari Satgas.</p>
    </div>

    @if (session('success'))
        <div class="privacy-notice" role="status">
            <span aria-hidden="true">✓</span>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('tiket.lookup') }}" class="panel ticket-search" novalidate>
        @csrf
        <label for="ticketCode">Kode tiket</label>
        <input id="ticketCode" name="ticket_code"
               value="{{ old('ticket_code', $ticket['ticket_code'] ?? null) }}"
               placeholder="PPKS-XXXX-XXXX-XXXX" maxlength="19" required>
        <small class="field-hint">Contoh format: PPKS-AB12-CD34-EF56</small>
        @error('ticket_code')
            <small class="field-hint form-error-inline">{{ $message }}</small>
        @enderror
        @error('api')
            <small class="field-hint form-error-inline">{{ $message }}</small>
        @enderror
        <button class="btn btn-primary" type="submit">Cek Tiket →</button>
    </form>

    @if (!empty($ticket))
        <section class="panel ticket-result">
            <h2>{{ $ticket['ticket_code'] ?? 'Tiket' }}</h2>

            @php
                $status = $ticket['status'] ?? null;
                $statusClass = match ($status) {
                    'SELESAI', 'DONE' => 'status-done',
                    'SEDANG_DIDAMPINGI', 'PROSES' => 'status-progress',
                    default => 'status-waiting',
                };
                $statusLabel = match ($status) {
                    'SELESAI', 'DONE' => 'Selesai',
                    'SEDANG_DIDAMPINGI', 'PROSES' => 'Sedang Diproses',
                    'MENUNGGU_TIM', 'WAITING' => 'Menunggu',
                    default => $status ?: 'Tidak diketahui',
                };
                $updated = $ticket['updated_at'] ?? $ticket['updated'] ?? $ticket['created_at'] ?? null;
            @endphp

            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
            @if ($updated)
                <p>Terakhir diperbarui: {{ $updated }}</p>
            @endif

            <div class="privacy-notice" style="margin-top:14px">
                <span aria-hidden="true">♧</span>
                <p>Isi laporan tidak ditampilkan di halaman ini untuk menjaga privasi.</p>
            </div>

            <h3>Pesan dari Satgas</h3>
            @forelse ($ticket['messages'] ?? [] as $message)
                <div class="message-card">
                    <small>{{ $message['sender'] ?? 'Satgas' }}</small>
                    <p>{{ $message['message'] ?? $message['text'] ?? '' }}</p>
                </div>
            @empty
                <p class="empty-state">Belum ada pesan dari Satgas.</p>
            @endforelse

            <form method="POST" action="{{ route('tiket.reply') }}" class="reply-form" novalidate>
                @csrf
                <input type="hidden" name="ticket_code" value="{{ $ticket['ticket_code'] ?? '' }}">
                <label for="ticketReply">Balasan pelapor</label>
                <textarea id="ticketReply" name="message" rows="4" maxlength="2000"
                          placeholder="Tulis balasan maksimal 2000 karakter...">{{ old('message') }}</textarea>
                @error('reply')
                    <small class="field-hint form-error-inline">{{ $message }}</small>
                @enderror
                <button class="btn btn-primary" type="submit">Kirim Balasan →</button>
            </form>
        </section>
    @endif
</div>
@endsection