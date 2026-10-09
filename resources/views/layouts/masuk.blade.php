@extends('layouts.app')

@section('title', 'Masuk Petugas | CampusCare')

@section('content')
<div class="login-page">
    <div class="login-visual">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-mark" aria-hidden="true">♥</span>
            <span><strong>CampusCare</strong><small>SATGAS PPKS KAMPUS</small></span>
        </a>

        <div class="login-intro">
            <span class="eyebrow">PORTAL PETUGAS</span>
            <h1>Bersama membangun kampus yang aman.</h1>
            <p>Kelola laporan, pantau tindak lanjut, dan dukung pelapor melalui satu dashboard.</p>
        </div>

        <div class="login-illustration" aria-hidden="true">♧</div>
    </div>

    <div class="login-content">
        <form method="POST" action="{{ route('masuk.proses') }}" class="login-form" novalidate>
            @csrf
            <span class="eyebrow">SELAMAT DATANG KEMBALI</span>
            <h2>Masuk ke Dashboard</h2>
            <p>Gunakan akun petugas yang diberikan oleh administrator.</p>

            @error('login')
                <div class="form-error" role="alert">{{ $message }}</div>
            @enderror

            <div class="field-group">
                <label for="username">Username</label>
                <input id="username" name="username" autocomplete="username"
                       value="{{ old('username') }}" required placeholder="Masukkan username">
            </div>

            <div class="field-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password"
                       autocomplete="current-password" required placeholder="Masukkan password">
            </div>

            <div class="field-group">
                <label for="twoFactor">Kode 2FA (jika tersedia)</label>
                <input id="twoFactor" name="two_factor_code" inputmode="numeric"
                       autocomplete="one-time-code" placeholder="Masukkan kode verifikasi">
            </div>

            <button type="submit" class="btn btn-primary full-width">Masuk →</button>
            <a href="{{ route('home') }}" class="text-link">← Kembali ke halaman utama</a>
        </form>
    </div>
</div>
@endsection