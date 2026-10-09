@props(['variant' => 'dark', 'showTagline' => true, 'compact' => false])

@php
    $isLight = $variant === 'light';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl {{ $isLight ? 'bg-white/15' : 'bg-brand-100' }}">
        <svg viewBox="0 0 24 24" class="h-6 w-6 {{ $isLight ? 'text-white' : 'text-brand-700' }}" role="img" aria-label="Perisai perlindungan">
            <path fill="currentColor" d="M12 2 4 5v6c0 4.9 3.4 9.5 8 10.7 4.6-1.2 8-5.8 8-10.7V5l-8-3Z" opacity="0.18"/>
            <path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" d="M12 2 4 5v6c0 4.9 3.4 9.5 8 10.7 4.6-1.2 8-5.8 8-10.7V5l-8-3Z"/>
            <path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M12 14.5s3.2-1.9 3.2-4.2A1.7 1.7 0 0 0 12 9.3a1.7 1.7 0 0 0-3.2 1c0 2.3 3.2 4.2 3.2 4.2Z"/>
        </svg>
    </span>
    <span class="flex flex-col leading-tight">
        <span class="text-[17px] font-extrabold tracking-tight {{ $isLight ? 'text-white' : 'text-ink-900' }}">{{ config('app.name', 'SATGAS PPKS') }}</span>
        @if ($showTagline)
            <span class="text-[11px] font-semibold uppercase tracking-[0.18em] {{ $isLight ? 'text-brand-100' : 'text-brand-700' }}">SATGAS PPKS</span>
        @endif
    </span>
</span>
