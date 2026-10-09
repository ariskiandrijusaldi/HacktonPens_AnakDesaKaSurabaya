@extends('layouts.app')

@section('title', 'Buat Laporan | CampusCare')

@section('content')
<div class="container page-space">
    <div class="section-title">
        <span class="eyebrow">FORMULIR LAPORAN</span>
        <h1>Buat Laporan</h1>
        <p>Ceritakan kejadian yang ingin kamu sampaikan. Isi hanya informasi yang nyaman untuk kamu bagikan.</p>
    </div>

    @if (session('report_success'))
        <section class="panel success-card" role="status">
            <div class="success-icon" aria-hidden="true">✓</div>
            <h2>Laporan kamu berhasil diterima</h2>
            <p>Simpan kode tiket ini untuk memantau status laporanmu.</p>
            <div class="ticket-large">{{ session('ticket_code') }}</div>
            <a href="{{ route('tiket') }}" class="btn btn-primary">Cek Status Tiket →</a>
        </section>
    @else
    <div class="form-layout">
        <form class="panel report-form" method="POST"
              action="{{ route('lapor.store') }}" enctype="multipart/form-data" novalidate>
            @csrf

            @if ($errors->has('api'))
                <div class="form-error" role="alert">{{ $errors->first('api') }}</div>
            @endif

            <div class="field-group">
                <label for="reportText">Ceritakan kejadian yang kamu alami *</label>
                <textarea id="reportText" name="description" rows="7"
                          minlength="20" maxlength="20000" required
                          placeholder="Ceritakan kejadian yang ingin kamu laporkan...">{{ old('description') }}</textarea>
                @error('description')
                    <small class="field-hint form-error-inline">{{ $message }}</small>
                @else
                    <small class="field-hint">Tulis sedetail yang kamu rasa nyaman. Minimal 20 karakter.</small>
                @enderror
            </div>

            <div class="field-group">
                <label for="victimWish">Apa yang kamu harapkan dari laporan ini? *</label>
                <select id="victimWish" name="victim_wish" required>
                    <option value="">Pilih harapan kamu…</option>
                    @foreach ([
                        'KONSULTASI' => 'Konsultasi atau informasi',
                        'PENDAMPINGAN' => 'Pendampingan dan tindak lanjut',
                        'PROSES_FORMAL' => 'Bantuan proses formal',
                        'BELUM_TAHU' => 'Belum yakin, ingin berdiskusi',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('victim_wish') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('victim_wish')
                    <small class="field-hint form-error-inline">{{ $message }}</small>
                @enderror
            </div>

            <div class="field-group">
                <label for="location">Lokasi umum (opsional)</label>
                <input id="location" name="location" maxlength="500"
                       value="{{ old('location') }}"
                       placeholder="Contoh: Gedung A, area perpustakaan">
                <small class="field-hint">Masukkan lokasi umum saja, jangan alamat lengkap atau lokasi pribadi yang terperinci.</small>
            </div>

            <div class="field-group">
                <label for="contact">Kontak (opsional)</label>
                <input id="contact" name="contact" maxlength="500" autocomplete="off"
                       value="{{ old('contact') }}"
                       placeholder="Nomor telepon atau email">
                <small class="field-hint">Jika memberikan kontak, kamu menyetujui kemungkinan dihubungi petugas. Kontak merupakan informasi sensitif.</small>
                @error('contact_consent')
                    <small class="field-hint form-error-inline">{{ $message }}</small>
                @enderror
                <label class="consent-box">
                    <input type="checkbox" name="contact_consent" value="1"
                           @checked(old('contact_consent'))>
                    <span>Setuju untuk dihubungi melalui kontak tersebut bila diperlukan.</span>
                </label>
            </div>

            <div class="field-group">
                <label for="attachments">Lampiran pendukung (maksimal 3 file)</label>
                <div class="upload-box">
                    <span class="upload-symbol" aria-hidden="true">↑</span>
                    <strong>Pilih file pendukung</strong>
                    <small>JPG, PNG, atau PDF. Maksimal 5 MB per file.</small>
                    <input id="attachments" name="attachments[]" type="file"
                           accept=".jpg,.jpeg,.png,.pdf" multiple>
                </div>
                @error('attachments.*')
                    <small class="field-hint form-error-inline">{{ $message }}</small>
                @enderror
            </div>

            <label class="consent-box">
                <input type="checkbox" name="consent" value="1" @checked(old('consent')) required>
                <span>
                    Saya menyatakan bahwa informasi ini diberikan dengan sadar,
                    memahami tujuan pelaporan, dan menyetujui peninjauan oleh
                    petugas berwenang. *
                </span>
            </label>
            @error('consent')
                <small class="field-hint form-error-inline">{{ $message }}</small>
            @enderror

            <button type="submit" class="btn btn-primary full-width">
                Kirim Laporan →
            </button>
        </form>

        <aside class="form-aside">
            <div class="panel">
                <h3>♧ Privasi laporan</h3>
                <p>Bagikan informasi seperlunya. Laporan disamarkan dan semua keputusan tetap ditinjau oleh petugas.</p>
                <hr>
                <h3>♡ Pilihanmu penting</h3>
                <p>Kamu boleh memilih kebutuhan pendampingan yang paling sesuai dengan keadaanmu.</p>
            </div>

            <div class="panel">
                <h3>Kontak Darurat</h3>
                <p>Jika berada dalam bahaya langsung, cari tempat aman dan hubungi layanan yang sesuai.</p>
                <div class="emergency-contact">
                    <div><strong>SAPA 129</strong><small>Perempuan dan anak</small></div>
                    <a href="tel:129">129 ↗</a>
                </div>
                <div class="emergency-contact">
                    <div><strong>Polisi</strong><small>Bantuan darurat</small></div>
                    <a href="tel:110">110 ↗</a>
                </div>
            </div>
        </aside>
    </div>
    @endif
</div>
@endsection