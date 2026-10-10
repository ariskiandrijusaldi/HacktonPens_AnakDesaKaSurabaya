@extends('layouts.app')

@section('title', 'Masuk Petugas | CampusCare')

@push('head')
<style>
  /* Gaya halaman Portal Petugas: satu keluarga dengan Beranda/Bantuan (merah merek, tombol pil, tipografi besar). */
  .pt{--ink:#111;--mute:#444;--line:#ddd;--brand:#eb0a1e;--soft:#f4f4f4;font-family:"Segoe UI",Inter,Arial,sans-serif;color:var(--ink)}
  .pt *{box-sizing:border-box}
  .pt a{color:inherit;text-decoration:none}
  .pt :focus-visible{outline:3px solid #2b7fff;outline-offset:2px}

  .pt-page{background:linear-gradient(135deg,#16222d 0%,#0f1720 100%)}
  .pt-grid{width:100%;max-width:1200px;min-height:calc(100vh - 80px);margin:0 auto;padding:clamp(48px,7vw,96px) clamp(16px,5vw,40px);display:grid;grid-template-columns:1.05fr .95fr;gap:clamp(32px,5vw,72px);align-items:center}

  /* Kolom kiri: pengantar di atas latar gelap */
  .pt-brand{display:inline-flex;align-items:center;gap:12px;margin-bottom:28px;color:#fff}
  .pt-mark{display:grid;place-items:center;width:46px;height:46px;background:var(--brand);color:#fff;font-size:22px}
  .pt-brand strong{display:block;font-size:20px;letter-spacing:.02em}
  .pt-brand small{display:block;font-size:12px;letter-spacing:.06em;color:rgba(255,255,255,.72)}
  .pt-eyebrow{display:inline-block;margin-bottom:14px;padding-left:12px;border-left:4px solid var(--brand);font-size:14px;letter-spacing:.04em;color:rgba(255,255,255,.8)}
  .pt-intro h1{margin:0;max-width:16ch;font-size:clamp(30px,4vw,52px);font-weight:700;line-height:1.1;color:#fff}
  .pt-intro p{margin:16px 0 0;max-width:46ch;font-size:clamp(15px,1.5vw,19px);line-height:1.7;color:rgba(255,255,255,.82)}

  /* Kolom kanan: kartu form */
  .pt-card{display:grid;gap:16px;padding:clamp(28px,4vw,44px);background:#fff;border:1px solid var(--line);border-top:4px solid var(--brand);border-radius:6px;box-shadow:0 24px 60px rgba(0,0,0,.28)}
  .pt-tag{font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--brand)}
  .pt-card h2{margin:0;font-size:clamp(24px,3vw,32px);font-weight:700}
  .pt-sub{margin:0;color:var(--mute);font-size:15px;line-height:1.6}
  .pt-field{display:grid;gap:7px}
  .pt-field label{font-size:14px;font-weight:600}
  .pt-input{height:52px;padding:0 16px;border:1px solid var(--line);border-radius:8px;background:#fff;font-size:16px}
  .pt-input:focus{border-color:var(--ink);outline:none}
  .pt-error{padding:12px 14px;border:1px solid #f1c6cb;border-radius:6px;background:#fff0f1;color:#a92736;font-size:14px}
  .pt-pill{display:inline-flex;align-items:center;justify-content:center;height:50px;margin-top:4px;border:0;border-radius:999px;background:#111;color:#fff;font-weight:600;font-size:16px;cursor:pointer;transition:background .25s}
  .pt-pill:hover{background:#333}
  .pt-link{font-size:14px;font-weight:600;text-align:center;color:var(--mute)}
  .pt-link:hover{color:var(--brand)}

  @media(max-width:860px){
    .pt-grid{grid-template-columns:1fr;min-height:0}
    .pt-intro h1{max-width:none}
  }
</style>
@endpush

@section('content')
<div class="pt">
    <div class="pt-page">
        <div class="pt-grid">
            <div class="pt-intro">
                <a href="{{ route('home') }}" class="pt-brand">
                    <span class="pt-mark" aria-hidden="true">♥</span>
                    <span><strong>CampusCare</strong><small>SATGAS PPKS KAMPUS</small></span>
                </a>

                <span class="pt-eyebrow">Pusat layanan petugas</span>
                <h1>Bersama membangun kampus yang aman.</h1>
                <p>Kelola laporan, pantau tindak lanjut, dan dukung pelapor melalui satu dashboard.</p>
            </div>

            <form method="POST" action="{{ route('masuk.proses') }}" class="pt-card" novalidate>
                @csrf
                <span class="pt-tag">Selamat datang kembali</span>
                <h2>Masuk ke Dashboard</h2>
                <p class="pt-sub">Gunakan akun petugas yang diberikan oleh administrator.</p>

                @error('login')
                    <div class="pt-error" role="alert">{{ $message }}</div>
                @enderror

                <div class="pt-field">
                    <label for="username">Username</label>
                    <input id="username" class="pt-input" name="username" autocomplete="username"
                           value="{{ old('username') }}" required placeholder="Masukkan username">
                </div>

                <div class="pt-field">
                    <label for="password">Password</label>
                    <input id="password" class="pt-input" name="password" type="password"
                           autocomplete="current-password" required placeholder="Masukkan password">
                </div>

                <div class="pt-field">
                    <label for="twoFactor">Kode 2FA (jika tersedia)</label>
                    <input id="twoFactor" class="pt-input" name="two_factor_code" inputmode="numeric"
                           autocomplete="one-time-code" placeholder="Masukkan kode verifikasi">
                </div>

                <button type="submit" class="pt-pill">Masuk →</button>
                <a href="{{ route('home') }}" class="pt-link">← Kembali ke halaman utama</a>
            </form>
        </div>
    </div>
</div>
@endsection
