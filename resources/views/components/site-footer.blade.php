<footer class="mt-20 bg-ink-900 text-ink-300">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-4">
            <div class="md:col-span-2">
                <x-logo variant="light" />
                <p class="mt-4 max-w-md text-sm leading-relaxed text-ink-400">
                    Ruang aman untuk melapor. Identitasmu disamarkan otomatis, tanpa akun, tanpa pelacak, dan tanpa menyimpan alamat IP.
                </p>
            </div>
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-white">Bantuan</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="/bantuan" class="hover:text-white">Kontak Darurat</a></li>
                    <li><a href="/bantuan#p3" class="hover:text-white">Pertolongan Pertama Psikologis</a></li>
                    <li><a href="/#transparansi" class="hover:text-white">Privasi</a></li>
                    <li><a href="/tiket" class="hover:text-white">Cek Tiket</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-white">Darurat</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="tel:129" rel="noreferrer noopener" class="hover:text-white">SAPA 129</a></li>
                    <li><a href="tel:110" rel="noreferrer noopener" class="hover:text-white">Polisi 110</a></li>
                    <li><a href="/masuk" class="hover:text-white">Masuk Petugas</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-12 flex flex-col gap-3 border-t border-white/10 pt-6 text-xs text-ink-400 sm:flex-row sm:items-center sm:justify-between">
            <p>© <span data-year>2026</span> {{ config('app.name', 'SATGAS PPKS') }}. Titik aman kampus.</p>
            <p>Situs ini mengutamakan keselamatanmu.</p>
        </div>
    </div>
</footer>
