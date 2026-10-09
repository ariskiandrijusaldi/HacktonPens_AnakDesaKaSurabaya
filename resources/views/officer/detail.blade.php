@extends('layouts.app')

@section('title', 'Detail Laporan | CampusCare')

@section('content')
@php
    $report = $report ?? [];
    $id = $report['id'] ?? null;
    $ticket = $report['ticket_code'] ?? '—';
    $urgency = $report['urgency_final'] ?? $report['urgency_ai'] ?? null;
    $status = $report['status'] ?? null;
    $notes = $report['notes'] ?? [];
    $messages = $report['messages'] ?? [];
    $attachments = $report['attachments'] ?? [];
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
@endphp
<div class="officer-layout">
    <x-officer-sidebar active="detail" />

    <section class="officer-main">
        <header class="dashboard-header">
            <div>
                <a href="{{ route('dasbor') }}" class="text-link">← Kembali ke daftar</a>
                <h1>Detail Laporan {{ $ticket }}</h1>
                <p>Informasi laporan yang disamarkan untuk penanganan petugas.</p>
            </div>
            <span class="urgency {{ $urgencyClass }}">{{ $urgency ?: 'N/A' }}</span>
        </header>

        <div class="dashboard-content">
            @if ($errors->any())
                <div class="form-error" role="alert">
                    @foreach ($errors->all() as $error){{ $error }} @endforeach
                </div>
            @endif
            @if (session('success'))
                <div class="privacy-notice" role="status">
                    <span aria-hidden="true">✓</span>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="detail-grid">
                <div class="detail-main">
                    <section class="panel">
                        <div class="panel-heading">
                            <h2>Ringkasan Laporan</h2>
                            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                        <div class="privacy-notice">
                            <span aria-hidden="true">♧</span>
                            <p>Kontak dan teks asli hanya dapat diakses melalui prosedur Override oleh ADMIN.</p>
                        </div>

                        @if (!empty($report['summary']))
                            <h3>Ringkasan</h3>
                            <p>{{ $report['summary'] }}</p>
                        @endif

                        @if (!empty($report['category']))
                            <h3>Kategori</h3>
                            <p>{{ $report['category'] }}</p>
                        @endif

                        @if (!empty($report['urgency_reason']))
                            <h3>Alasan urgensitas</h3>
                            <p>{{ $report['urgency_reason'] }}</p>
                        @endif

                        <div class="detail-facts">
                            <div><small>Kode tiket</small><strong>{{ $ticket }}</strong></div>
                            <div><small>Kategori</small><strong>{{ $report['category'] ?? 'Lainnya' }}</strong></div>
                            <div><small>Lokasi umum</small><strong>{{ $report['location'] ?? '—' }}</strong></div>
                            <div><small>Keinginan pelapor</small><strong>{{ $report['victim_wish'] ?? '—' }}</strong></div>
                            @if (!empty($report['created_at']))
                                <div><small>Diinput</small><strong>{{ $report['created_at'] }}</strong></div>
                            @endif
                            @if (!empty($report['assigned_to']))
                                <div><small>Petugas</small><strong>{{ $report['assigned_to'] }}</strong></div>
                            @endif
                        </div>
                    </section>

                    <section class="panel">
                        <h2>Catatan Internal</h2>
                        <div class="message-list">
                            @forelse ($notes as $note)
                                <div class="message-card">
                                    <small>{{ $note['created_at'] ?? '' }}</small>
                                    <p>{{ $note['text'] ?? $note['note'] ?? $note['message'] ?? '' }}</p>
                                </div>
                            @empty
                                <p class="empty-state">Belum ada catatan internal.</p>
                            @endforelse
                        </div>
                        @if ($id)
                            <form method="POST" action="{{ route('laporan.catatan', $id) }}" class="reply-form">
                                @csrf
                                <label for="internalNote">Tambah catatan internal</label>
                                <textarea id="internalNote" name="note" rows="3" maxlength="5000"
                                          required placeholder="Catatan untuk petugas...">{{ old('note') }}</textarea>
                                <button type="submit" class="btn btn-primary">Tambah Catatan</button>
                            </form>
                        @endif
                    </section>

                    <section class="panel">
                        <h2>Pesan kepada Pelapor</h2>
                        <div class="message-list">
                            @forelse ($messages as $message)
                                <div class="message-card">
                                    <small>{{ $message['sender'] ?? 'Satgas' }}</small>
                                    <p>{{ $message['message'] ?? $message['text'] ?? '' }}</p>
                                </div>
                            @empty
                                <p class="empty-state">Belum ada pesan untuk pelapor.</p>
                            @endforelse
                        </div>
                        @if ($id)
                            <form method="POST" action="{{ route('laporan.pesan', $id) }}" class="reply-form">
                                @csrf
                                <label for="officerMessage">Tulis pesan</label>
                                <textarea id="officerMessage" name="message" rows="3" maxlength="2000"
                                          placeholder="Tulis pesan yang akan dikirim...">{{ old('message') }}</textarea>
                                <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                            </form>
                        @endif
                    </section>
                </div>

                <aside class="detail-side">
                    @if ($id)
                        <section class="panel">
                            <h3>Ubah Status</h3>
                            <form method="POST" action="{{ route('laporan.status', $id) }}">
                                @csrf
                                @method('PATCH')
                                <label for="reportStatus">Status laporan</label>
                                <select id="reportStatus" name="status">
                                    @foreach ([
                                        'MENUNGGU_TIM' => 'Menunggu',
                                        'SEDANG_DIDAMPINGI' => 'Diproses',
                                        'SELESAI' => 'Selesai',
                                    ] as $value => $label)
                                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary full-width">Simpan Status</button>
                            </form>

                            <hr>

                            <h3>Ubah Urgensi</h3>
                            <form method="POST" action="{{ route('laporan.urgensi', $id) }}">
                                @csrf
                                @method('PATCH')
                                <label for="reportUrgency">Tingkat urgensi</label>
                                <select id="reportUrgency" name="urgency">
                                    @foreach (['MERAH', 'KUNING', 'HIJAU'] as $value)
                                        <option value="{{ $value }}" @selected($urgency === $value)>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <label for="urgencyReason">Alasan perubahan (wajib)</label>
                                <textarea id="urgencyReason" name="reason" rows="3" minlength="10" maxlength="2000"
                                          required placeholder="Jelaskan alasan perubahan urgensi...">{{ old('reason') }}</textarea>
                                <button type="submit" class="btn btn-primary full-width">Simpan Urgensi</button>
                            </form>

                            <hr>

                            <h3>Tugaskan Petugas</h3>
                            <form method="POST" action="{{ route('laporan.assign', $id) }}">
                                @csrf
                                @method('PATCH')
                                <label for="assignedOfficer">Petugas yang bertugas</label>
                                <input id="assignedOfficer" name="assigned_to" maxlength="100"
                                       value="{{ $report['assigned_to'] ?? old('assigned_to') }}"
                                       placeholder="Nama petugas">
                                <button type="submit" class="btn btn-outline full-width">Tugaskan</button>
                            </form>
                        </section>
                    @endif

                    @if (!empty($attachments))
                        <section class="panel">
                            <h3>Lampiran</h3>
                            @foreach ($attachments as $file)
                                @php $fileId = $file['id'] ?? $file['file_id'] ?? null; @endphp
                                @if ($id && $fileId)
                                    <a class="table-action" href="{{ route('laporan.lampiran', [$id, $fileId]) }}">
                                        Unduh {{ $file['filename'] ?? 'lampiran' }} ↘
                                    </a>
                                @else
                                    <p class="field-hint">{{ $file['filename'] ?? 'Lampiran tersedia' }}</p>
                                @endif
                            @endforeach
                        </section>
                    @endif

                    @if (!empty($report['status_history']))
                        <section class="panel">
                            <h3>Riwayat Perubahan</h3>
                            @foreach ($report['status_history'] as $entry)
                                <div class="message-card">
                                    <small>{{ $entry['created_at'] ?? '' }}</small>
                                    <p>{{ $entry['description'] ?? $entry['note'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </section>
                    @endif
                </aside>
            </div>
        </div>
    </section>
</div>
@endsection