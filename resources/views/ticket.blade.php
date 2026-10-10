@extends('layouts.app')

@section('title', 'Cek Status Laporan | CampusCare')

@push('head')
<style>
  /* Gaya halaman Cek Status: satu keluarga dengan Beranda/Bantuan (merah merek, tombol pil, tipografi besar). */
  .ck{--ink:#111;--mute:#444;--line:#ddd;--brand:#eb0a1e;--soft:#f4f4f4;font-family:"Segoe UI",Inter,Arial,sans-serif;color:var(--ink)}
  .ck *{box-sizing:border-box}
  .ck a{color:inherit;text-decoration:none}
  .ck :focus-visible{outline:3px solid #2b7fff;outline-offset:2px}
  .ck-wrap{width:100%;max-width:1200px;margin:0 auto;padding:0 clamp(16px,5vw,40px)}
  .ck-main{padding-bottom:clamp(48px,7vw,88px)}

  /* Kepala halaman gelap, selaras dengan navbar dan Beranda */
  .ck-head{background:linear-gradient(135deg,#16222d 0%,#0f1720 100%);color:#fff;padding:clamp(48px,8vw,96px) 0 clamp(96px,12vw,140px)}
  .ck-head h1{margin:0;font-size:clamp(32px,5vw,56px);font-weight:700;line-height:1.08;max-width:18ch}
  .ck-head p{margin:16px 0 0;max-width:62ch;font-size:clamp(16px,1.6vw,20px);line-height:1.6;color:rgba(255,255,255,.85)}
  .ck-eyebrow{display:inline-block;margin-bottom:14px;padding-left:12px;border-left:4px solid var(--brand);font-size:14px;letter-spacing:.04em;color:rgba(255,255,255,.8)}

  /* Kartu umum */
  .ck-card{background:#fff;border:1px solid var(--line);border-radius:6px;box-shadow:0 12px 30px rgba(0,0,0,.12)}

  /* Form pelacakan menimpa kepala halaman */
  .ck-search{position:relative;z-index:2;margin-top:clamp(-96px,-8vw,-72px);display:grid;gap:14px;padding:clamp(24px,4vw,36px);border-top:4px solid var(--brand)}
  .ck-search label{font-weight:600;font-size:15px}
  .ck-input{width:100%;height:52px;padding:0 18px;border:1px solid var(--line);border-radius:8px;font-size:18px;letter-spacing:.04em}
  .ck-input:focus{border-color:var(--ink);outline:none}
  .ck-hint{margin:0;font-size:13px;color:var(--mute)}
  .ck-error{margin:0;font-size:13px;font-weight:600;color:#b92939}

  /* Tombol pil, sama dengan halaman lain */
  .ck-pill{display:inline-flex;align-items:center;justify-content:center;height:46px;padding:0 28px;border:0;border-radius:999px;background:#111;color:#fff!important;font-weight:600;font-size:16px;cursor:pointer;transition:background .25s}
  .ck-pill:hover{background:#333}
  .ck-pill.alt{background:#fff;color:#111!important;border:2px solid #111}
  .ck-pill.alt:hover{background:var(--soft)}
  .ck-search .ck-pill{justify-self:start}

  /* Notifikasi */
  .ck-notice{display:flex;gap:12px;align-items:flex-start;padding:16px 18px;background:var(--soft);border-left:4px solid var(--brand);border-radius:4px}
  .ck-notice p{margin:0;font-size:14px;color:var(--mute)}

  /* Hasil pelacakan */
  .ck-result{margin-top:28px;padding:clamp(24px,4vw,36px)}
  .ck-result-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px}
  .ck-ticket{display:block;font-size:clamp(20px,3vw,28px);font-weight:700;letter-spacing:.02em;overflow-wrap:anywhere}
  .ck-legend{margin:6px 0 0;font-size:13px;color:var(--mute)}
  .ck-sub{margin:22px 0 0;font-size:20px;font-weight:700}
  .ck-msg{padding:16px 18px;margin-top:12px;border:1px solid var(--line);border-radius:6px;background:#fafafa}
  .ck-msg small{display:block;margin-bottom:6px;font-size:12px;letter-spacing:.04em;text-transform:uppercase;color:var(--mute)}
  .ck-msg p{margin:0;line-height:1.6}
  .ck-empty{margin:14px 0 0;color:var(--mute);font-size:15px}

  /* Form balasan */
  .ck-reply{display:grid;gap:12px;margin-top:22px}
  .ck-reply label{font-weight:600;font-size:15px}
  .ck-textarea{width:100%;min-height:110px;padding:14px 16px;border:1px solid var(--line);border-radius:8px;font:inherit;line-height:1.6;resize:vertical}
  .ck-textarea:focus{border-color:var(--ink);outline:none}
  .ck-reply .ck-pill{justify-self:start}

  @media(max-width:700px){
    .ck-search{border-radius:0}
  }
</style>
@endpush

@section('content')
<div class="ck">
    <header class="ck-head">
        <div class="ck-wrap">
            <span class="ck-eyebrow">Pelacakan laporan</span>
            <h1>Cek Status Laporan</h1>
            <p>Masukkan kode tiket yang kamu terima untuk melihat status dan pesan dari Satgas.</p>
        </div>
    </header>

    <div class="ck-wrap ck-main">
        <form method="POST" action="{{ route('tiket.lookup') }}" class="ck-card ck-search" novalidate>
            @csrf
            <label for="ticketCode">Kode tiket</label>
            <input id="ticketCode" class="ck-input" name="ticket_code"
                   value="{{ old('ticket_code', $ticket['ticket_code'] ?? null) }}"
                   placeholder="PPKS-XXXXXX-XXXXXX" maxlength="18" required>
            <p class="ck-hint">Contoh format: PPKS-123456-789012</p>
            @error('ticket_code')
                <p class="ck-error">{{ $message }}</p>
            @enderror
            @error('api')
                <p class="ck-error">{{ $message }}</p>
            @enderror
            <button class="ck-pill" type="submit">Cek Tiket →</button>
        </form>

        @if (session('success'))
            <div class="ck-notice" style="margin-top:24px" role="status">
                <span aria-hidden="true">✓</span>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if (!empty($ticket))
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

            <section class="ck-card ck-result">
                <div class="ck-result-head">
                    <div>
                        <span class="ck-ticket">{{ $ticket['ticket_code'] ?? 'Tiket' }}</span>
                        @if ($updated)
                            <p class="ck-legend">Terakhir diperbarui: {{ $updated }}</p>
                        @endif
                    </div>
                    <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>

                <div class="ck-notice" style="margin-top:18px">
                    <span aria-hidden="true">♧</span>
                    <p>Isi laporan tidak ditampilkan di halaman ini untuk menjaga privasi.</p>
                </div>

                <h3 class="ck-sub">Pesan dari Satgas</h3>
                @forelse ($ticket['messages'] ?? [] as $message)
                    <div class="ck-msg">
                        <small>{{ $message['sender'] ?? 'Satgas' }}</small>
                        <p>{{ $message['body'] ?? $message['message'] ?? $message['text'] ?? '' }}</p>
                    </div>
                @empty
                    <p class="ck-empty">Belum ada pesan dari Satgas.</p>
                @endforelse

                <form method="POST" action="{{ route('tiket.reply') }}" class="ck-reply" novalidate>
                    @csrf
                    <input type="hidden" name="ticket_code" value="{{ $ticket['ticket_code'] ?? '' }}">
                    <label for="replyMessage">Balas pesan</label>
                    <textarea id="replyMessage" name="message" class="ck-textarea" maxlength="2000"
                              placeholder="Tulis balasan untuk Satgas…">{{ old('message') }}</textarea>
                    @error('reply')
                        <p class="ck-error">{{ $message }}</p>
                    @enderror
                    <button class="ck-pill" type="submit">Kirim Balasan →</button>
                </form>
            </section>
        @endif
    </div>
</div>
@endsection
