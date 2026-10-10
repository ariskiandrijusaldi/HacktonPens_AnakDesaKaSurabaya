@props(['active' => null])

@php
    $path = trim(request()->path(), '/');
    $items = [
        ['href' => '/dasbor', 'label' => 'Dasbor', 'match' => 'dasbor', 'admin' => false],
        ['href' => '/dasbor/laporan', 'label' => 'Laporan', 'match' => 'dasbor/laporan', 'admin' => false],
        ['href' => '/dasbor/statistik', 'label' => 'Statistik', 'match' => 'dasbor/statistik', 'admin' => false],
        ['href' => '/dasbor/pola', 'label' => 'Pola', 'match' => 'dasbor/pola', 'admin' => false],
        ['href' => '/dasbor/audit', 'label' => 'Audit', 'match' => 'dasbor/audit', 'admin' => true],
    ];
    $isMatch = function (string $match) use ($path) {
        if ($match === 'dasbor') {
            return $path === 'dasbor';
        }

        return $path === $match || str_starts_with($path, $match.'/');
    };
@endphp

<header class="sticky top-0 z-50 border-b border-ink-200 bg-white">
    <div class="mx-auto flex h-20 max-w-[1400px] items-center gap-4 px-4 sm:px-6 lg:px-8">
        <a href="/dasbor" class="inline-flex shrink-0 items-center gap-3" aria-label="CAMPUSCARE — dasbor petugas">
            <span class="grid h-11 w-11 shrink-0 place-items-center bg-brand-600 text-white" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M12 21s-7-4.6-9.3-9.2C1.1 8.5 3 5 6.4 5c2 0 3.6 1.1 5.6 3 2-1.9 3.6-3 5.6-3C21 5 22.9 8.5 21.3 11.8 19 16.4 12 21 12 21z"/></svg>
            </span>
            <span class="flex flex-col leading-tight">
                <strong class="text-[20px] font-extrabold tracking-tight text-ink-900">CAMPUSCARE</strong>
                <small class="text-[11px] font-medium text-ink-500">Layanan Satgas PPKS Kampus</small>
            </span>
        </a>

        <nav class="ml-3 hidden items-center gap-7 lg:flex" aria-label="Navigasi petugas">
            @foreach ($items as $item)
                @php $isActive = $isMatch($item['match']); @endphp
                <a href="{{ $item['href'] }}"
                   data-nav-match="{{ $item['match'] }}"
                   @if ($item['admin']) data-admin-only @endif
                   class="border-b-2 pb-1 text-[16px] transition-colors {{ $isActive ? 'border-brand-600 font-semibold text-ink-900' : 'border-transparent font-medium text-ink-500 hover:border-ink-900 hover:text-ink-900' }}"
                   @if ($isActive) aria-current="page" @endif>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-2">
            <span data-connection-indicator data-state="connecting"
                  class="hidden items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-500 lg:inline-flex">
                <span data-connection-dot class="h-2.5 w-2.5 rounded-full bg-ink-400"></span>
                <span data-connection-label>Menghubungkan…</span>
            </span>

            <button type="button" data-alarm-toggle
                    class="grid h-11 w-11 place-items-center rounded-full text-ink-500 hover:bg-ink-100"
                    aria-pressed="false" aria-label="Aktifkan suara alarm">
                <svg data-alarm-icon-off viewBox="0 0 20 20" class="h-5 w-5" aria-hidden="true"><path fill="currentColor" d="M10 2a5 5 0 0 0-5 5v3l-1.6 2.6A1 1 0 0 0 4.3 14h11.4a1 1 0 0 0 .9-1.4L15 10V7a5 5 0 0 0-5-5Zm0 16a2.5 2.5 0 0 0 2.4-1.8H7.6A2.5 2.5 0 0 0 10 18Z"/></svg>
                <svg data-alarm-icon-on viewBox="0 0 20 20" class="hidden h-5 w-5 text-exit-600" aria-hidden="true"><path fill="currentColor" d="M10 2a5 5 0 0 0-5 5v3l-1.6 2.6A1 1 0 0 0 4.3 14h11.4a1 1 0 0 0 .9-1.4L15 10V7a5 5 0 0 0-5-5Zm0 16a2.5 2.5 0 0 0 2.4-1.8H7.6A2.5 2.5 0 0 0 10 18Z"/><circle cx="15.5" cy="5" r="2.5" class="fill-exit-500"/></svg>
            </button>

            <div class="hidden items-center gap-2 rounded-full border border-ink-200 py-1.5 pl-1.5 pr-3 lg:flex">
                <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white" aria-hidden="true">
                    <span data-officer-initial>P</span>
                </span>
                <span class="text-sm font-semibold text-ink-700" data-officer-name>Petugas</span>
                <span data-role-badge class="rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-bold uppercase text-brand-700" data-role-label>ANGGOTA</span>
            </div>

            <form method="POST" action="{{ route('keluar') }}">
                @csrf
                <button type="submit" class="inline-flex min-h-11 items-center gap-2 rounded-full bg-ink-900 px-5 text-sm font-semibold text-white transition-colors hover:bg-ink-800">
                    <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path fill="currentColor" d="M11 3a1 1 0 0 1 1-1h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3a1 1 0 1 1 0-2h3V4h-3a1 1 0 0 1-1-1ZM7.7 6.3a1 1 0 0 1 0 1.4L6.4 9H12a1 1 0 1 1 0 2H6.4l1.3 1.3a1 1 0 1 1-1.4 1.4l-3-3a1 1 0 0 1 0-1.4l3-3a1 1 0 0 1 1.4 0Z"/></svg>
                    <span class="hidden sm:inline">Keluar</span>
                </button>
            </form>

            <button type="button" data-mobile-toggle aria-expanded="false" aria-controls="menu-officer"
                    class="grid h-11 w-11 place-items-center rounded-full text-ink-700 hover:bg-ink-100 lg:hidden" aria-label="Buka menu">
                <svg viewBox="0 0 20 20" class="h-6 w-6" aria-hidden="true"><path fill="currentColor" d="M3 5a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1Zm0 5a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1Zm1 4a1 1 0 1 0 0 2h12a1 1 0 1 0 0-2H4Z"/></svg>
            </button>
        </div>
    </div>

    <div id="menu-officer" data-mobile-panel class="hidden border-t border-ink-200 bg-white lg:hidden">
        <nav class="mx-auto flex max-w-[1400px] flex-col gap-1 px-4 py-4" aria-label="Navigasi petugas seluler">
            @foreach ($items as $item)
                @php $isActive = $isMatch($item['match']); @endphp
                <a href="{{ $item['href'] }}"
                   @if ($item['admin']) data-admin-only @endif
                   class="flex min-h-11 items-center rounded-md px-3 py-2 text-[15px] font-medium {{ $isActive ? 'bg-brand-50 font-semibold text-brand-700' : 'text-ink-600 hover:bg-ink-100' }}">{{ $item['label'] }}</a>
            @endforeach
            <form method="POST" action="{{ route('keluar') }}" class="p-2">
                @csrf
                <button type="submit" class="btn-ghost justify-start">Keluar</button>
            </form>
        </nav>
    </div>
</header>
