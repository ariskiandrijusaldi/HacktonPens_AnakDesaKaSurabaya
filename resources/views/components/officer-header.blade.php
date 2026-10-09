@props(['active' => null])

@php
    $path = trim(request()->path(), '/');
    $items = [
        ['href' => '/dasbor', 'label' => 'Dasbor', 'match' => 'dasbor', 'admin' => false],
        ['href' => '/dasbor/statistik', 'label' => 'Statistik', 'match' => 'dasbor/statistik', 'admin' => false],
        ['href' => '/dasbor/pola', 'label' => 'Pola', 'match' => 'dasbor/pola', 'admin' => false],
        ['href' => '/dasbor/audit', 'label' => 'Audit', 'match' => 'dasbor/audit', 'admin' => true],
    ];
@endphp

<header class="sticky top-0 z-50 border-b border-ink-100 bg-white shadow-[0_1px_0_rgba(0,0,0,0.04)]">
    <div class="mx-auto flex h-20 max-w-[1400px] items-center gap-3 px-4 sm:px-6 lg:px-8">
        <a href="/dasbor" class="shrink-0" aria-label="SATGAS PPKS, dasbor petugas">
            <x-logo :show-tagline="false" />
        </a>

        <nav class="ml-4 hidden items-center gap-1 lg:flex" aria-label="Navigasi petugas">
            @foreach ($items as $item)
                @php $isActive = $path === $item['match']; @endphp
                <a href="{{ $item['href'] }}"
                   data-nav-match="{{ $item['match'] }}"
                   @if ($item['admin']) data-admin-only @endif
                   class="nav-link {{ $isActive ? 'bg-brand-50 text-brand-700' : '' }}"
                   @if ($isActive) aria-current="page" @endif>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-2">
            <span data-connection-indicator data-state="connecting"
                  class="hidden items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-600 sm:inline-flex">
                <span data-connection-dot class="h-2.5 w-2.5 rounded-full bg-ink-400"></span>
                <span data-connection-label>Menghubungkan…</span>
            </span>

            <button type="button" data-alarm-toggle
                    class="grid h-11 w-11 place-items-center rounded-full text-ink-600 hover:bg-ink-100"
                    aria-pressed="false" aria-label="Aktifkan suara alarm">
                <svg data-alarm-icon-off viewBox="0 0 20 20" class="h-5 w-5" aria-hidden="true"><path fill="currentColor" d="M10 2a5 5 0 0 0-5 5v3l-1.6 2.6A1 1 0 0 0 4.3 14h11.4a1 1 0 0 0 .9-1.4L15 10V7a5 5 0 0 0-5-5Zm0 16a2.5 2.5 0 0 0 2.4-1.8H7.6A2.5 2.5 0 0 0 10 18Z"/></svg>
                <svg data-alarm-icon-on viewBox="0 0 20 20" class="hidden h-5 w-5 text-exit-600" aria-hidden="true"><path fill="currentColor" d="M10 2a5 5 0 0 0-5 5v3l-1.6 2.6A1 1 0 0 0 4.3 14h11.4a1 1 0 0 0 .9-1.4L15 10V7a5 5 0 0 0-5-5Zm0 16a2.5 2.5 0 0 0 2.4-1.8H7.6A2.5 2.5 0 0 0 10 18Z"/><circle cx="15.5" cy="5" r="2.5" class="fill-exit-500"/></svg>
            </button>

            <div class="hidden items-center gap-2 rounded-full bg-ink-100 py-1.5 pl-1.5 pr-3 sm:flex">
                <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-700 text-sm font-bold text-white" aria-hidden="true">
                    <span data-officer-initial>P</span>
                </span>
                <span class="text-sm font-semibold text-ink-700" data-officer-name>Petugas</span>
                <span data-role-badge class="rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-bold uppercase text-brand-700" data-role-label>ANGGOTA</span>
            </div>

            <button type="button" data-logout class="btn-ghost btn-sm">
                <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path fill="currentColor" d="M11 3a1 1 0 0 1 1-1h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3a1 1 0 1 1 0-2h3V4h-3a1 1 0 0 1-1-1ZM7.7 6.3a1 1 0 0 1 0 1.4L6.4 9H12a1 1 0 1 1 0 2H6.4l1.3 1.3a1 1 0 1 1-1.4 1.4l-3-3a1 1 0 0 1 0-1.4l3-3a1 1 0 0 1 1.4 0Z"/></svg>
                <span class="hidden sm:inline">Keluar</span>
            </button>

            <button type="button" data-mobile-toggle aria-expanded="false" aria-controls="menu-officer"
                    class="grid h-11 w-11 place-items-center rounded-full text-ink-700 hover:bg-ink-100 lg:hidden" aria-label="Buka menu">
                <svg viewBox="0 0 20 20" class="h-6 w-6" aria-hidden="true"><path fill="currentColor" d="M3 5a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1Zm0 5a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1Zm1 4a1 1 0 1 0 0 2h12a1 1 0 1 0 0-2H4Z"/></svg>
            </button>
        </div>
    </div>

    <div id="menu-officer" data-mobile-panel class="hidden border-t border-ink-100 bg-white lg:hidden">
        <nav class="mx-auto flex max-w-[1400px] flex-col gap-1 px-4 py-4" aria-label="Navigasi petugas seluler">
            @foreach ($items as $item)
                <a href="{{ $item['href'] }}" @if ($item['admin']) data-admin-only @endif class="nav-link">{{ $item['label'] }}</a>
            @endforeach
            <button type="button" data-logout class="btn-ghost justify-start">Keluar</button>
        </nav>
    </div>
</header>
