@props(['active' => null])

@php
    $steps = [
        1 => 'Menunggu Tim',
        2 => 'Sedang Didampingi',
        3 => 'Selesai',
    ];
    $current = $active ?? 1;
@endphp

<ol class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-0" aria-label="Tahapan laporan">
    @foreach ($steps as $number => $label)
        <li class="flex flex-1 items-center gap-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-sm font-bold
                {{ $number < $current ? 'bg-brand-700 text-white' : ($number === $current ? 'bg-brand-100 text-brand-800 ring-2 ring-brand-600' : 'bg-ink-100 text-ink-400') }}"
                aria-hidden="true">
                @if ($number < $current)
                    <svg viewBox="0 0 20 20" class="h-5 w-5"><path fill="currentColor" d="M16.7 6.3 8.9 14a1 1 0 0 1-1.4 0l-3.2-3.2a1 1 0 1 1 1.4-1.4l2.5 2.5 7.1-7.1a1 1 0 0 1 1.4 1.4Z"/></svg>
                @else
                    {{ $number }}
                @endif
            </span>
            <span class="text-sm font-semibold {{ $number <= $current ? 'text-ink-800' : 'text-ink-400' }}">{{ $label }}</span>
            @if (! $loop->last)
                <span class="mx-4 hidden h-0.5 flex-1 rounded bg-ink-200 sm:block" aria-hidden="true"></span>
            @endif
        </li>
    @endforeach
</ol>
