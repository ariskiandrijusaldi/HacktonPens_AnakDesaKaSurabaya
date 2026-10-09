@extends('layouts.public')

@section('title', 'Masuk Petugas — SATGAS PPKS')

@section('content')
    <div class="mx-auto flex max-w-md flex-col px-4 py-16 sm:px-6">
        <div class="text-center">
            <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-brand-700">Ruang Petugas</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight text-ink-900">Masuk untuk mendampingi.</h1>
            <p class="mt-3 text-ink-600">Akses terbatas untuk Satgas PPKS yang berwenang.</p>
        </div>

        <div id="login-message" class="mt-6 hidden rounded-xl border-l-4 border-exit-500 bg-exit-500/10 p-4 text-sm font-medium text-ink-700" role="status" aria-live="polite"></div>

        <form id="login-form" class="mt-6 card border border-ink-100 p-6" novalidate>
            <div class="space-y-5">
                <div>
                    <label for="username" class="label">Nama pengguna</label>
                    <input id="username" name="username" type="text" autocomplete="username" required class="input" placeholder="nama.pengguna">
                </div>
                <div>
                    <label for="password" class="label">Kata sandi</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="input" placeholder="••••••••">
                </div>
                <div>
                    <label for="totp" class="label">Kode 2FA <span class="font-normal text-ink-400">(jika diaktifkan)</span></label>
                    <input id="totp" name="totp_code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="8"
                           class="input font-mono tracking-[0.3em]" placeholder="000000">
                </div>
            </div>

            <p id="login-error" class="field-error mt-4" role="alert" aria-live="assertive"></p>

            <button type="submit" id="login-button" class="btn-primary mt-6 w-full py-3">
                <span data-login-label>Masuk</span>
                <svg data-login-spinner viewBox="0 0 24 24" class="hidden h-5 w-5 animate-spin" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="3" opacity="0.25"/><path fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" d="M21 12a9 9 0 0 0-9-9"/></svg>
            </button>

            <p class="mt-4 text-center text-xs text-ink-500">
                Sesi berakhir 30 menit setelah masuk. Jangan tinggalkan perangkat ini tanpa diawasi.
            </p>
        </form>

        <div class="mt-6 rounded-xl bg-ink-50 p-4 text-sm text-ink-600">
            Otorisasi sebenarnya dilakukan oleh layanan backend. Halaman ini hanya menjaga kesopanan akses di sisi klien.
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/masuk.js')
@endpush
