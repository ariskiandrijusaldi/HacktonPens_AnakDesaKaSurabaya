@props(['active' => 'dasbor'])

@php
    $user = session('campuscare_user', []);
    $name = $user['name'] ?? $user['username'] ?? 'Petugas Satgas';
    $role = strtoupper($user['role'] ?? 'ANGGOTA');
    $roleLabel = $role === 'ADMIN' ? 'Admin' : 'Anggota';
    $initial = strtoupper(substr(trim($name), 0, 1) ?: 'P');
@endphp

<aside class="sidebar">
    <a href="{{ route('home') }}" class="brand sidebar-brand">
        <span class="brand-mark" aria-hidden="true">♥</span>
        <span><strong>CampusCare</strong><small>SATGAS PPKS</small></span>
    </a>

    <div class="sidebar-label">MENU UTAMA</div>
    <nav class="sidebar-nav" aria-label="Navigasi petugas">
        <a href="{{ route('dasbor') }}" @class(['active' => $active === 'dasbor'])>
            <span aria-hidden="true">▦</span> Dasbor
        </a>
        <a href="{{ route('statistik') }}" @class(['active' => $active === 'statistik'])>
            <span aria-hidden="true">▥</span> Statistik
        </a>
        <a href="{{ route('pola') }}" @class(['active' => $active === 'pola'])>
            <span aria-hidden="true">♧</span> Pola Terlapor
        </a>
        @if ($role === 'ADMIN')
            <a href="{{ route('audit') }}" @class(['active' => $active === 'audit'])>
                <span aria-hidden="true">☷</span> Audit Log
            </a>
        @endif
    </nav>

    <div class="sidebar-bottom">
        <div class="officer-profile">
            <span class="avatar" aria-hidden="true">{{ $initial }}</span>
            <div>
                <strong>{{ $name }}</strong>
                <small>{{ $roleLabel }} — Satgas PPKS</small>
            </div>
        </div>
        <form method="POST" action="{{ route('keluar') }}" onsubmit="return confirm('Keluar dari akun petugas?');">
            @csrf
            <button type="submit" class="logout-link">Keluar ↗</button>
        </form>
    </div>
</aside>