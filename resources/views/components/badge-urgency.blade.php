@props(['urgency' => null, 'size' => 'md'])

@php
    $map = [
        'MERAH' => ['label' => 'Merah', 'b' => 'border-alert-700 bg-alert-600 text-white'],
        'KUNING' => ['label' => 'Kuning', 'b' => 'border-exit-600 bg-exit-500 text-ink-900'],
        'HIJAU' => ['label' => 'Hijau', 'b' => 'border-brand-800 bg-brand-700 text-white'],
    ];
    $info = $map[$urgency] ?? ['label' => $urgency ?: 'Tidak diketahui', 'b' => 'border-ink-300 bg-ink-100 text-ink-700'];
    $sizeClass = $size === 'sm' ? 'px-2.5 py-1 text-xs gap-1' : 'px-3 py-1.5 text-sm gap-1.5';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border font-bold uppercase tracking-wide $info[b] $sizeClass"]) }}>
    @if ($urgency === 'MERAH')
        <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path fill="currentColor" d="M10 2 1.5 17h17L10 2Zm0 5.5a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Zm0 7a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"/></svg>
    @elseif ($urgency === 'KUNING')
        <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path fill="currentColor" d="M10 1 1 18h18L10 1Zm0 5a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0V7a1 1 0 0 1 1-1Zm0 8a1.1 1.1 0 1 1 0 2.2A1.1 1.1 0 0 1 10 14Z"/></svg>
    @elseif ($urgency === 'HIJAU')
        <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path fill="currentColor" d="M10 1.5a8.5 8.5 0 1 0 0 17 8.5 8.5 0 0 0 0-17Zm4.3 6.1-5 5a1 1 0 0 1-1.4 0l-2.2-2.2a1 1 0 0 1 1.4-1.4l1.5 1.5 4.3-4.3a1 1 0 0 1 1.4 1.4Z"/></svg>
    @endif
    {{ $info['label'] }}
</span>
