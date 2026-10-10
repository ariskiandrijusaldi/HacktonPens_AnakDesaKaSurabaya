@extends('layouts.officer')

@section('title', 'Dashboard Petugas | CampusCare')

@section('content')
<div class="officer-layout">
    <section class="officer-main">
        <header class="dashboard-header">
            <button type="button" class="mobile-menu" aria-label="Buka menu">☰</button>
            <div>
                <h1>Dashboard Petugas</h1>
                <p>Panel kontrol untuk mengelola laporan kekerasan seksual di kampus</p>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Laporan Masuk -->
                <a href="{{ route('dasbor') }}" class="card card-hover">
                    <div class="card-icon bg-blue-100 text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Laporan Masuk</h3>
                        <p class="card-description">Lihat dan kelola semua laporan masuk</p>
                    </div>
                </a>

                <!-- Statistik -->
                <a href="{{ route('statistik') }}" class="card card-hover">
                    <div class="card-icon bg-green-100 text-green-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Statistik</h3>
                        <p class="card-description">Lihat analisis dan statistik laporan</p>
                    </div>
                </a>

                <!-- Pola Terlapor -->
                <a href="{{ route('pola') }}" class="card card-hover">
                    <div class="card-icon bg-purple-100 text-purple-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Pola Terlapor</h3>
                        <p class="card-description">Deteksi pola dan terlapor berulang</p>
                    </div>
                </a>

                <!-- Audit Log -->
                @if (session('campuscare_user.role') === 'ADMIN')
                    <a href="{{ route('audit') }}" class="card card-hover">
                        <div class="card-icon bg-red-100 text-red-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Audit Log</h3>
                            <p class="card-description">Catatan aktivitas petugas</p>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection