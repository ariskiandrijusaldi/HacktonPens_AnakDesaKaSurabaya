@extends('layouts.app')

@section('title', 'Pusat Bantuan & Layanan Darurat | CampusCare')

@section('content')
<div class="container page-space">
    <div class="section-title">
        <span class="eyebrow">DUKUNGAN DAN PENDAMPINGAN</span>
        <h1>Pusat Bantuan & Layanan Darurat</h1>
        <p>Pilih layanan yang sesuai dengan kebutuhanmu. Jika berada dalam bahaya langsung, cari tempat aman dan hubungi bantuan darurat.</p>
    </div>

    <div class="emergency-strip">
        <div>
            <span class="emergency-icon" aria-hidden="true">♡</span>
            <div><strong>SAPA 129</strong><small>Layanan perempuan dan anak</small></div>
            <a href="tel:129" class="contact-number">129</a>
        </div>
        <div>
            <span class="emergency-icon" aria-hidden="true">+</span>
            <div><strong>Polisi</strong><small>Bantuan darurat</small></div>
            <a href="tel:110" class="contact-number">110</a>
        </div>
    </div>

    @if (!empty($resources))
        <div class="help-grid">
            @foreach ($resources as $resource)
                @php
                    $name = $resource['nama'] ?? $resource['name'] ?? $resource['title'] ?? null;
                    $desc = $resource['deskripsi'] ?? $resource['description'] ?? $resource['desc'] ?? null;
                    $phone = $resource['telepon'] ?? $resource['phone'] ?? $resource['kontak'] ?? null;
                    $url = $resource['website'] ?? $resource['url'] ?? $resource['link'] ?? null;
                    if (!$name) { continue; }
                @endphp
                <article class="feature-card help-card">
                    <span class="feature-icon" aria-hidden="true">♡</span>
                    <h3>{{ $name }}</h3>
                    @if ($desc)<p>{{ $desc }}</p>@endif
                    @if ($phone)
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="btn btn-primary">
                            Hubungi {{ $phone }}
                        </a>
                    @endif
                    @if ($url)
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-link">Situs resmi ↗</a>
                    @endif
                </article>
            @endforeach
        </div>
    @else
        <div class="help-grid">
            <article class="feature-card help-card">
                <span class="feature-icon" aria-hidden="true">♡</span>
                <h3>SAPA 129</h3>
                <p>Layanan Sahabat Perempuan dan Anak.</p>
                <a href="tel:129" class="btn btn-primary">Hubungi 129</a>
                <a href="https://laporsapa129.kemenpppa.go.id/" target="_blank" rel="noopener noreferrer" class="text-link">Situs resmi ↗</a>
            </article>

            <article class="feature-card help-card">
                <span class="feature-icon" aria-hidden="true">+</span>
                <h3>Polisi</h3>
                <p>Hubungi layanan kepolisian jika membutuhkan bantuan darurat.</p>
                <a href="tel:110" class="btn btn-primary">Hubungi 110</a>
                <a href="https://polri.go.id/" target="_blank" rel="noopener noreferrer" class="text-link">Situs resmi ↗</a>
            </article>

            <article class="feature-card help-card">
                <span class="feature-icon" aria-hidden="true">♧</span>
                <h3>Satgas PPKS Kampus</h3>
                <p>Hubungi unit resmi kampus untuk informasi pendampingan, perlindungan, dan prosedur setempat.</p>
                <a href="{{ route('lapor') }}" class="btn btn-outline">Buat Laporan</a>
            </article>
        </div>
    @endif

    <div class="panel">
        <h3>Ingat, kamu berhak mendapatkan bantuan.</h3>
        <p>Jika belum siap melapor, kamu dapat mempelajari pilihan bantuan yang tersedia terlebih dahulu.</p>
    </div>
</div>
@endsection