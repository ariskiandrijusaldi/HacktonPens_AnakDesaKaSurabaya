@extends('layouts.app')

@section('title', 'Beranda | CampusCare')

@section('content')

<div class="container">

    {{-- HERO SECTION --}}
    <section class="hero">

        <div class="hero-copy">

            <span class="safe-label">
                ✓ Ruang aman untuk mencari bantuan
            </span>

            <h1>
                Kamu tidak sendirian.
                <span>Kamu yang menentukan langkahnya.</span>
            </h1>

            <p>
                CampusCare hadir untuk mendengarkan, membantu, dan mendampingi
                kamu. Laporkan secara aman dan anonim, tanpa membuatmu takut.
            </p>

            <div class="hero-actions">

                <a href="{{ route('lapor') }}" class="btn btn-primary">
                    ✎ Buat Laporan →
                </a>

                <a href="{{ route('tiket') }}" class="btn btn-outline">
                    ◉ Cek Tiket
                </a>

                <a href="{{ route('bantuan') }}" class="btn btn-outline">
                    ♡ Bantuan Darurat
                </a>

            </div>

            {{-- INFORMASI KEAMANAN --}}
            <div class="trust-row">

                <div class="trust-item">
                    <span class="trust-icon">♙</span>

                    <div>
                        <strong>Identitas Disamarkan</strong>
                        <p>Privasi dan keamananmu diutamakan.</p>
                    </div>
                </div>

                <div class="trust-item">
                    <span class="trust-icon">♡</span>

                    <div>
                        <strong>Kamu Didengarkan</strong>
                        <p>Petugas membantu meninjau laporan.</p>
                    </div>
                </div>

            </div>

            <div class="hero-links">
                <a href="{{ route('tiket') }}">
                    Cek status laporan →
                </a>

                <a href="{{ route('bantuan') }}">
                    Butuh bantuan sekarang? →
                </a>
            </div>

        </div>

        {{-- ILUSTRASI HERO --}}
        <div class="hero-art">

            <div class="art-sun"></div>

            <div class="art-cloud cloud-one"></div>
            <div class="art-cloud cloud-two"></div>

            <div class="art-building building-one"></div>
            <div class="art-building building-two"></div>

            <div class="art-plant">❧</div>

            <div class="art-person">
                <div class="person-hair"></div>
                <div class="person-face"></div>
                <div class="person-neck"></div>
                <div class="person-body"></div>
                <div class="person-bag"></div>
            </div>

            <div class="art-quote">
                Di sini,<br>
                kamu didengar.<br>
                Kamu berharga. ♡
            </div>

            <div class="art-caption">
                Kamu berhak merasa aman.
            </div>

        </div>

    </section>


    {{-- PRIVASI DAN TRANSPARANSI --}}
    <section class="privacy-section">

        <div class="privacy-intro">

            <span class="eyebrow">
                PRIVASI DAN TRANSPARANSI
            </span>

            <h2>Kami menjaga privasimu</h2>

            <p>
                Kamu berhak mendapatkan informasi mengenai proses pelaporan
                dan bagaimana informasi sensitif seharusnya ditangani.
            </p>

            <a href="{{ route('bantuan') }}" class="text-link">
                Pelajari bantuan →
            </a>

        </div>


        <article class="feature-card mint">

            <span class="feature-icon">♡</span>

            <h3>Pilihan di Tanganmu</h3>

            <p>
                Sampaikan keinginan dan kebutuhanmu ketika melapor.
            </p>

        </article>


        <article class="feature-card blue">

            <span class="feature-icon blue-icon">♙</span>

            <h3>Privasi Diperhatikan</h3>

            <p>
                Informasi sensitif perlu dibatasi kepada petugas berwenang.
            </p>

        </article>


        <article class="feature-card aqua">

            <span class="feature-icon aqua-icon">✓</span>

            <h3>Manusia Tetap Menangani</h3>

            <p>
                Keputusan tindak lanjut berada pada petugas berwenang.
            </p>

        </article>

    </section>


    {{-- INFORMASI SEBELUM MELAPOR --}}
    <section class="bottom-panels">

        <div class="notice-panel">

            <div class="notice-icon">ⓘ</div>

            <div>
                <h3>Sebelum kamu melanjutkan</h3>

                <ul>
                    <li>Gunakan bahasa yang nyaman untukmu.</li>
                    <li>Hindari membagikan data pribadi yang tidak diperlukan.</li>
                    <li>Pastikan kamu berada di tempat yang aman.</li>
                    <li>Pelajari informasi bantuan jika diperlukan.</li>
                </ul>
            </div>

        </div>


        <div class="emergency-panel">

            <div class="emergency-symbol">♡</div>

            <div>
                <h3>Butuh bantuan sekarang?</h3>

                <p>
                    Jika berada dalam bahaya langsung, cari tempat aman
                    dan hubungi layanan darurat yang sesuai.
                </p>

                <a href="{{ route('bantuan') }}" class="btn btn-outline">
                    Lihat Kontak Darurat →
                </a>
            </div>

        </div>

    </section>

</div>

@endsection
