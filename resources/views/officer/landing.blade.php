@extends('layouts.officer')

@section('title', 'Dasbor Petugas | CampusCare')

@section('content')
@php
    $user = session('campuscare_user', []);
    $nama = $user['username'] ?? $user['name'] ?? 'Petugas';
    $peran = strtoupper($user['role'] ?? 'ANGGOTA') === 'ADMIN' ? 'Administrator' : 'Anggota';
@endphp

{{-- Kepala halaman bergaya beranda --}}
<div class="-mx-4 -mt-8 sm:-mx-6 lg:-mx-8">
    <header class="band-dark px-4 py-12 text-white sm:px-6 sm:py-14 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <span class="of-eyebrow border-brand-600 text-white/80">Ruang Petugas</span>
            <h1 class="mt-4 text-3xl font-bold leading-tight sm:text-4xl">Selamat datang, {{ $nama }}.</h1>
            <p class="mt-3 max-w-2xl text-base leading-relaxed text-white/85 sm:text-lg">
                Dasbor Satgas PPKS untuk meninjau laporan, memantau statistik, dan melihat pola terlapor.
                Setiap keputusan tindak lanjut tetap berada di tangan petugas berwenang.
            </p>
        </div>
    </header>
</div>

<div class="mx-auto mt-10 max-w-6xl">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-ink-900">Menu utama</h2>
            <p class="mt-1 text-ink-500">Pilih bagian yang ingin kamu buka.</p>
        </div>
        <span class="rounded-full bg-brand-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-700">{{ $peran }}</span>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
        <a href="{{ route('laporan') }}" class="of-card group">
            <span class="of-card-icon" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
            </span>
            <span class="text-xl font-bold text-ink-900">Laporan Masuk</span>
            <span class="text-ink-500">Tinjau laporan, ubah status, dan dampingi korban.</span>
            <span class="mt-auto pt-2 font-semibold text-brand-700">Buka →</span>
        </a>

        <a href="{{ route('statistik') }}" class="of-card group">
            <span class="of-card-icon" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M8 17V9m4 8V5m4 12v-6"/></svg>
            </span>
            <span class="text-xl font-bold text-ink-900">Statistik</span>
            <span class="text-ink-500">Lihat agregat anonim laporan yang masuk.</span>
            <span class="mt-auto pt-2 font-semibold text-brand-700">Buka →</span>
        </a>

        <a href="{{ route('pola') }}" class="of-card group">
            <span class="of-card-icon" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v3m0 12v3M3 12h3m12 0h3M5.6 5.6l2.1 2.1m8.6 8.6 2.1 2.1m0-12.8-2.1 2.1M7.7 16.3l-2.1 2.1"/><circle cx="12" cy="12" r="3.2"/></svg>
            </span>
            <span class="text-xl font-bold text-ink-900">Pola Terlapor</span>
            <span class="text-ink-500">Deteksi indikasi terlapor yang berulang.</span>
            <span class="mt-auto pt-2 font-semibold text-brand-700">Buka →</span>
        </a>

        @if (session('campuscare_user.role') === 'ADMIN')
            <a href="{{ route('audit') }}" class="of-card group">
                <span class="of-card-icon" aria-hidden="true">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.6-4A12 12 0 0 1 12 3a12 12 0 0 1-8.6 3A12 12 0 0 0 3 9c0 5.6 3.8 10.3 9 11.6 5.2-1.3 9-6 9-11.6 0-1-.1-2-.4-2.9Z"/></svg>
                </span>
                <span class="text-xl font-bold text-ink-900">Audit Log</span>
                <span class="text-ink-500">Riwayat akses dan tindakan petugas.</span>
                <span class="mt-auto pt-2 font-semibold text-brand-700">Buka →</span>
            </a>
        @endif
    </div>

    <div class="mt-8 flex flex-col gap-3 rounded-md border border-ink-200 bg-ink-50 p-6 sm:flex-row sm:items-center">
        <span class="grid h-11 w-11 shrink-0 place-items-center bg-brand-600 text-white" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/></svg>
        </span>
        <div>
            <strong class="block text-ink-900">Setiap laporan bersifat rahasia.</strong>
            <p class="mt-1 text-sm text-ink-500">
                Identitas pelapor dan isi laporan asli dilindungi. Hanya ADMIN yang dapat membukanya dengan PIN,
                dan setiap akses tercatat di audit log.
            </p>
        </div>
    </div>
</div>
@endsection
