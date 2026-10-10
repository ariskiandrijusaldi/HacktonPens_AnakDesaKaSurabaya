@extends('layouts.app')

@section('title', 'Pusat Bantuan & Layanan Darurat | CampusCare')

@push('head')
<style>
  /* Gaya halaman Bantuan: satu keluarga dengan layout (merah merek, tombol pil, tipografi besar). */
  .bt{--ink:#111;--mute:#444;--line:#ddd;--brand:#eb0a1e;--soft:#f4f4f4;font-family:"Segoe UI",Inter,Arial,sans-serif;color:var(--ink)}
  .bt *{box-sizing:border-box}
  .bt a{color:inherit;text-decoration:none}
  .bt svg{width:24px;height:24px;flex:none}
  .bt :focus-visible{outline:3px solid #2b7fff;outline-offset:2px}
  .bt .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
  .bt-wrap{width:100%;max-width:1200px;margin:0 auto;padding:0 clamp(16px,5vw,40px)}

  /* Kepala halaman gelap, selaras dengan navbar */
  .bt-head{background:linear-gradient(135deg,#16222d 0%,#0f1720 100%);color:#fff;padding:clamp(48px,8vw,96px) 0 clamp(96px,12vw,140px)}
  .bt-head h1{margin:0;font-size:clamp(32px,5vw,56px);font-weight:700;line-height:1.08;max-width:18ch}
  .bt-head p{margin:16px 0 0;max-width:62ch;font-size:clamp(16px,1.6vw,20px);line-height:1.6;color:rgba(255,255,255,.85)}
  .bt-eyebrow{display:inline-block;margin-bottom:14px;padding-left:12px;border-left:4px solid var(--brand);font-size:14px;letter-spacing:.04em;color:rgba(255,255,255,.8)}

  /* Dua kartu darurat menimpa kepala halaman */
  .bt-urgent{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-top:clamp(-96px,-8vw,-72px);position:relative;z-index:2}
  .bt-urgent a{display:flex;align-items:center;gap:18px;padding:24px 28px;background:#fff;border:1px solid var(--line);border-top:4px solid var(--brand);border-radius:6px;box-shadow:0 12px 30px rgba(0,0,0,.12);transition:transform .25s,box-shadow .25s}
  .bt-urgent a:hover{transform:translateY(-2px);box-shadow:0 16px 36px rgba(0,0,0,.16)}
  .bt-urgent strong{display:block;font-size:20px}
  .bt-urgent small{display:block;margin-top:2px;font-size:15px;color:var(--mute)}
  .bt-urgent b{margin-left:auto;font-size:clamp(34px,4vw,48px);font-weight:700;color:var(--brand);line-height:1}
  .bt-ico{display:grid;place-items:center;width:48px;height:48px;background:var(--brand);color:#fff}

  /* Kartu layanan */
  .bt-sec{padding:clamp(40px,6vw,72px) 0 0}
  .bt-sec h2{margin:0 0 24px;font-size:clamp(24px,3vw,34px);font-weight:700}
  .bt-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}
  .bt-card{display:flex;flex-direction:column;gap:12px;padding:28px;background:#fff;border:1px solid var(--line);border-radius:6px;transition:border-color .25s,box-shadow .25s}
  .bt-card:hover{border-color:var(--ink);box-shadow:0 10px 26px rgba(0,0,0,.08)}
  .bt-card .bt-ico{width:44px;height:44px}
  .bt-card h3{margin:6px 0 0;font-size:22px}
  .bt-card p{margin:0;color:var(--mute);line-height:1.6;font-size:16px}
  .bt-actions{display:flex;flex-wrap:wrap;align-items:center;gap:14px;margin-top:auto;padding-top:12px}
  .bt-pill{display:inline-flex;align-items:center;justify-content:center;height:46px;padding:0 28px;border-radius:999px;background:#111;color:#fff!important;font-weight:600;font-size:16px;transition:background .25s}
  .bt-pill:hover{background:#333}
  .bt-pill.alt{background:#fff;color:#111!important;border:2px solid #111}
  .bt-pill.alt:hover{background:var(--soft)}
  .bt-link{font-weight:600;text-decoration:underline;text-underline-offset:3px}
  .bt-link:hover{color:var(--brand)}

  /* Panel penutup */
  .bt-panel{display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;margin:clamp(40px,6vw,72px) 0 clamp(48px,7vw,88px);padding:clamp(24px,4vw,40px);background:var(--soft);border-left:6px solid var(--brand);border-radius:6px}
  .bt-panel h3{margin:0;font-size:clamp(20px,2.4vw,28px)}
  .bt-panel p{margin:8px 0 0;max-width:60ch;color:var(--mute);line-height:1.6}

  @media(max-width:700px){
    .bt-urgent{grid-template-columns:1fr}
    .bt-urgent a{padding:20px}
  }
</style>
@endpush

@section('content')
@php
    // Ikon SVG yang dipakai ulang oleh tiap kartu.
    $ikon = [
        'heart'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7-4.6-9.3-9.2C1.1 8.5 3 5 6.4 5c2 0 3.6 1.1 5.6 3 2-1.9 3.6-3 5.6-3C21 5 22.9 8.5 21.3 11.8 19 16.4 12 21 12 21z"/></svg>',
        'plus'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>',
        'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/></svg>',
    ];

    // Satu daftar kartu: pakai data dari controller bila ada, jika tidak pakai bawaan.
    $cards = [];
    if (!empty($resources)) {
        foreach ($resources as $resource) {
            $name = $resource['nama'] ?? $resource['name'] ?? $resource['title'] ?? null;
            if (!$name) { continue; }
            $cards[] = [
                'ikon'  => 'heart',
                'nama'  => $name,
                'desc'  => $resource['deskripsi'] ?? $resource['description'] ?? $resource['desc'] ?? null,
                'phone' => $resource['telepon'] ?? $resource['phone'] ?? $resource['kontak'] ?? null,
                'url'   => $resource['website'] ?? $resource['url'] ?? $resource['link'] ?? null,
            ];
        }
    }
    if (empty($cards)) {
        $cards = [
            ['ikon' => 'heart',  'nama' => 'SAPA 129', 'desc' => 'Layanan Sahabat Perempuan dan Anak.', 'phone' => '129', 'url' => 'https://laporsapa129.kemenpppa.go.id/'],
            ['ikon' => 'plus',   'nama' => 'Polisi',   'desc' => 'Hubungi layanan kepolisian jika membutuhkan bantuan darurat.', 'phone' => '110', 'url' => 'https://polri.go.id/'],
            ['ikon' => 'shield', 'nama' => 'Satgas PPKS Kampus', 'desc' => 'Hubungi unit resmi kampus untuk informasi pendampingan, perlindungan, dan prosedur setempat.', 'phone' => null, 'url' => null, 'lapor' => true],
        ];
    }
@endphp

<div class="bt">
    <header class="bt-head">
        <div class="bt-wrap">
            <span class="bt-eyebrow">Dukungan dan pendampingan</span>
            <h1>Pusat Bantuan &amp; Layanan Darurat</h1>
            <p>Pilih layanan yang sesuai dengan kebutuhanmu. Jika berada dalam bahaya langsung, cari tempat aman dan hubungi bantuan darurat.</p>
        </div>
    </header>

    <div class="bt-wrap">
        <div class="bt-urgent">
            <a href="tel:129" aria-label="Telepon SAPA 129, layanan perempuan dan anak">
                <span class="bt-ico">{!! $ikon['heart'] !!}</span>
                <span><strong>SAPA 129</strong><small>Layanan perempuan dan anak</small></span>
                <b>129</b>
            </a>
            <a href="tel:110" aria-label="Telepon Polisi, bantuan darurat">
                <span class="bt-ico">{!! $ikon['plus'] !!}</span>
                <span><strong>Polisi</strong><small>Bantuan darurat</small></span>
                <b>110</b>
            </a>
        </div>

        <section class="bt-sec" aria-labelledby="judul-layanan">
            <h2 id="judul-layanan">Layanan yang bisa kamu hubungi</h2>
            <div class="bt-grid">
                @foreach ($cards as $c)
                    <article class="bt-card">
                        <span class="bt-ico">{!! $ikon[$c['ikon']] ?? $ikon['heart'] !!}</span>
                        <h3>{{ $c['nama'] }}</h3>
                        @if (!empty($c['desc']))<p>{{ $c['desc'] }}</p>@endif
                        <div class="bt-actions">
                            @if (!empty($c['phone']))
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $c['phone']) }}" class="bt-pill">Hubungi {{ $c['phone'] }}</a>
                            @endif
                            @if (!empty($c['lapor']))
                                <a href="{{ route('lapor') }}" class="bt-pill alt">Buat Laporan</a>
                            @endif
                            @if (!empty($c['url']))
                                <a href="{{ $c['url'] }}" target="_blank" rel="noopener noreferrer" class="bt-link">Situs resmi<span class="sr-only"> (buka di tab baru)</span> ↗</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="bt-panel">
            <div>
                <h3>Ingat, kamu berhak mendapatkan bantuan.</h3>
                <p>Jika belum siap melapor, kamu dapat mempelajari pilihan bantuan yang tersedia terlebih dahulu.</p>
            </div>
            <a href="{{ route('lapor') }}" class="bt-pill">Buat Laporan</a>
        </div>
    </div>
</div>
@endsection