@extends('layouts.officer')

@section('title', 'Detail Laporan | CampusCare')

@section('content')
@php
    $report = $report ?? [];
    $ticket = $report['ticket_code'] ?? '—';
    $summary = $report['summary'] ?? '';
    $sanitizedText = $report['sanitized_text'] ?? '';
    $rationale = $report['urgency_rationale'] ?? '';
    $urgency = $report['urgency_final'] ?? $report['urgency_ai'] ?? null;
    $status = $report['status'] ?? null;
    $category = ucfirst((string) ($report['category'] ?? 'Lainnya'));
    $location = $report['location'] ?? '—';
    $assignee = ! empty($report['assigned_to']) ? 'Sudah ditugaskan' : 'Belum ditugaskan';
    $waiting = (float) ($report['waiting_hours'] ?? 0);
    $aiFailed = (bool) ($report['ai_failed'] ?? false);
    $contactAvailable = (bool) ($report['contact_available'] ?? false);
    $attachments = $report['attachments'] ?? [];
    $notes = $report['notes'] ?? [];
    $messages = $report['messages'] ?? [];

    $urgencyLabel = match ($urgency) {
        'MERAH' => 'Merah',
        'KUNING' => 'Kuning',
        'HIJAU' => 'Hijau',
        default => $urgency ?: 'N/A',
    };
    $urgencyClass = match ($urgency) {
        'MERAH' => 'bg-red-100 text-red-700',
        'KUNING' => 'bg-yellow-100 text-yellow-700',
        'HIJAU' => 'bg-green-100 text-green-700',
        default => 'bg-ink-100 text-ink-600',
    };
    $statusLabel = match ($status) {
        'MENUNGGU_TIM' => 'Menunggu Tim',
        'SEDANG_DIDAMPINGI' => 'Sedang Didampingi',
        'SELESAI' => 'Selesai',
        default => $status ?: 'Tidak diketahui',
    };
    $statusClass = match ($status) {
        'SELESAI' => 'bg-green-100 text-green-700',
        'SEDANG_DIDAMPINGI' => 'bg-blue-100 text-blue-700',
        default => 'bg-amber-100 text-amber-800',
    };
    $wishLabel = match ($report['victim_wish'] ?? null) {
        'KONSULTASI' => 'Konsultasi',
        'PENDAMPINGAN' => 'Pendampingan',
        'PROSES_FORMAL' => 'Proses Formal',
        'BELUM_TAHU' => 'Belum Tahu',
        default => $report['victim_wish'] ?? 'Tidak disebutkan',
    };
    $createdAt = $report['created_at'] ?? null;
    $updatedAt = $report['updated_at'] ?? null;
    $fmt = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d M Y, H:i') : '—';
    $fmtNote = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d M Y, H:i') : '';
@endphp

<x-officer-band title="Detail Laporan" :description="$ticket">
    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $urgencyClass }}">Urgensi {{ $urgencyLabel }}</span>
    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
</x-officer-band>

<section class="mx-auto mt-8 max-w-6xl">
    <a href="{{ route('laporan') }}" class="text-sm font-semibold text-ink-500 transition-colors hover:text-ink-900">← Kembali ke Laporan Masuk</a>

    @if (session('success'))
        <div class="mt-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-xl bg-alert-50 px-4 py-3 text-sm font-medium text-alert-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            <div class="card p-6">
                <h2 class="text-lg font-bold text-ink-900">Ringkasan</h2>
                <p class="mt-2 text-ink-700">{{ $summary }}</p>
                <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-ink-400">Kategori</dt>
                        <dd class="mt-0.5 text-sm font-medium text-ink-800">{{ $category }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-ink-400">Lokasi</dt>
                        <dd class="mt-0.5 text-sm font-medium text-ink-800">{{ $location }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-ink-400">Keinginan Korban</dt>
                        <dd class="mt-0.5 text-sm font-medium text-ink-800">{{ $wishLabel }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-ink-400">Petugas Ditugaskan</dt>
                        <dd class="mt-0.5 text-sm font-medium text-ink-800">{{ $assignee }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-ink-400">Dibuat</dt>
                        <dd class="mt-0.5 text-sm font-medium text-ink-800">{{ $fmt($createdAt) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-ink-400">Diperbarui</dt>
                        <dd class="mt-0.5 text-sm font-medium text-ink-800">{{ $fmt($updatedAt) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-ink-400">Waktu Menunggu</dt>
                        <dd class="mt-0.5 text-sm font-medium text-ink-800">
                            {{ $waiting > 0 ? number_format($waiting, 1).' jam' : 'Kurang dari 1 jam' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-ink-400">Triase AI</dt>
                        <dd class="mt-0.5 text-sm font-medium text-ink-800">{{ $aiFailed ? 'Gagal — perlu tinjauan manual' : 'Berhasil' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="card p-6">
                <h2 class="text-lg font-bold text-ink-900">Isi Laporan</h2>
                <p class="mt-2 whitespace-pre-line text-ink-700">{{ $sanitizedText ?: 'Tidak ada isi laporan yang dapat ditampilkan.' }}</p>
            </div>

            @if ($rationale !== '')
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-ink-900">Analisis AI</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-ink-600">{{ $rationale }}</p>
                </div>
            @endif

            <div class="card p-6">
                <h2 class="text-lg font-bold text-ink-900">Lampiran</h2>
                @if (count($attachments) === 0)
                    <p class="mt-2 text-sm text-ink-500">Tidak ada lampiran untuk laporan ini.</p>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach ($attachments as $attachment)
                            <li>
                                <a href="{{ route('laporan.lampiran', [$report['id'], $attachment['id']]) }}"
                                   class="flex items-center justify-between rounded-xl border border-ink-200 bg-ink-50 px-4 py-3 transition-colors hover:border-brand-500">
                                    <span class="flex items-center gap-3">
                                        <svg class="h-5 w-5 shrink-0 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7.172a4.5 4.5 0 01-6.364 0L4.586 2.95a4.5 4.5 0 016.364-6.364l6.708 6.708a6 6 0 01-8.486 8.486L3.93 5.293"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-ink-700">
                                            Lampiran · {{ strtoupper(str_replace('application/', '', $attachment['mime'] ?? '')) }}
                                            <span class="text-ink-400">({{ number_format(($attachment['size'] ?? 0) / 1024, 1) }} KB)</span>
                                        </span>
                                    </span>
                                    <span class="text-sm font-semibold text-brand-700">Unduh →</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="flex flex-col gap-6">
            <div class="card p-6">
                <h2 class="text-lg font-bold text-ink-900">Kontak Korban</h2>
                @if ($contactAvailable)
                    <p class="mt-2 text-sm text-ink-600">
                        Kontak pelapor tersedia dan <span class="font-semibold text-ink-800">dilindungi</span>.
                        Identitas hanya dapat dibuka oleh administrator dengan verifikasi PIN dan dicatat di audit log.
                    </p>
                @else
                    <p class="mt-2 text-sm text-ink-500">Pelapor tidak menyertakan kontak.</p>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="text-lg font-bold text-ink-900">Tindakan Petugas</h2>
                <div class="mt-4 space-y-5">
                    <form method="POST" action="{{ route('laporan.status', $report['id']) }}">
                        @csrf
                        @method('PATCH')
                        <label for="status" class="label">Ubah Status</label>
                        <select id="status" name="status" class="input">
                            <option value="MENUNGGU_TIM" @selected($status === 'MENUNGGU_TIM')>Menunggu Tim</option>
                            <option value="SEDANG_DIDAMPINGI" @selected($status === 'SEDANG_DIDAMPINGI')>Sedang Didampingi</option>
                            <option value="SELESAI" @selected($status === 'SELESAI')>Selesai</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm mt-3">Simpan Status</button>
                    </form>

                    <form method="POST" action="{{ route('laporan.urgensi', $report['id']) }}">
                        @csrf
                        @method('PATCH')
                        <label for="urgency" class="label">Ubah Urgensi</label>
                        <select id="urgency" name="urgency" class="input">
                            <option value="HIJAU" @selected($urgency === 'HIJAU')>Hijau</option>
                            <option value="KUNING" @selected($urgency === 'KUNING')>Kuning</option>
                            <option value="MERAH" @selected($urgency === 'MERAH')>Merah</option>
                        </select>
                        <label for="reason" class="label mt-3">Alasan Perubahan</label>
                        <textarea id="reason" name="reason" rows="3" class="input" required></textarea>
                        <p class="hint">Minimal 10 karakter.</p>
                        <button type="submit" class="btn btn-primary btn-sm mt-3">Simpan Urgensi</button>
                    </form>

                    <form method="POST" action="{{ route('laporan.assign', $report['id']) }}">
                        @csrf
                        @method('PATCH')
                        <label for="assigned_to" class="label">Tugaskan Petugas</label>
                        <input id="assigned_to" name="assigned_to" type="text" class="input" placeholder="Username petugas">
                        <p class="hint">Kosongkan untuk melepas penugasan.</p>
                        <button type="submit" class="btn btn-primary btn-sm mt-3">Simpan Penugasan</button>
                    </form>
                </div>
            </div>

            <div class="card p-6">
                <h2 class="text-lg font-bold text-ink-900">Catatan Internal</h2>

                @if (count($notes) === 0)
                    <p class="mt-2 text-sm text-ink-500">Belum ada catatan internal.</p>
                @else
                    <ul class="mt-3 space-y-3">
                        @foreach ($notes as $note)
                            <li class="rounded-xl bg-ink-50 px-4 py-3">
                                <p class="whitespace-pre-line text-sm text-ink-700">{{ $note['note'] }}</p>
                                <p class="mt-1 text-xs text-ink-400">{{ $fmtNote($note['created_at'] ?? null) }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <form method="POST" action="{{ route('laporan.catatan', $report['id']) }}" class="mt-4">
                    @csrf
                    <label for="note" class="label">Tambah Catatan</label>
                    <textarea id="note" name="note" rows="3" class="input" placeholder="Catatan internal untuk tim…" required></textarea>
                    <button type="submit" class="btn btn-primary btn-sm mt-3">Simpan Catatan</button>
                </form>
            </div>

            <div class="card p-6">
                <h2 class="text-lg font-bold text-ink-900">Percakapan</h2>

                @if (count($messages) === 0)
                    <p class="mt-2 text-sm text-ink-500">Belum ada percakapan.</p>
                @else
                    <ul class="mt-3 space-y-3">
                        @foreach ($messages as $message)
                            <li class="rounded-xl bg-ink-50 px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-ink-400">
                                    {{ $message['sender'] === 'SATGAS' ? 'Petugas' : 'Pelapor' }}
                                </p>
                                <p class="mt-1 whitespace-pre-line text-sm text-ink-700">{{ $message['body'] }}</p>
                                <p class="mt-1 text-xs text-ink-400">{{ $fmtNote($message['created_at'] ?? null) }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <form method="POST" action="{{ route('laporan.pesan', $report['id']) }}" class="mt-4">
                    @csrf
                    <label for="message" class="label">Balas Pesan</label>
                    <textarea id="message" name="message" rows="3" class="input" placeholder="Tulis balasan untuk pelapor…" required></textarea>
                    <button type="submit" class="btn btn-primary btn-sm mt-3">Kirim Balasan</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection