@props(['status' => null, 'size' => 'md'])

@php
    $map = [
        'MENUNGGU_TIM' => ['label' => 'Menunggu Tim', 'b' => 'border-ink-300 bg-ink-100 text-ink-700'],
        'SEDANG_DIDAMPINGI' => ['label' => 'Sedang Didampingi', 'b' => 'border-brand-500 bg-brand-100 text-brand-800'],
        'SELESAI' => ['label' => 'Selesai', 'b' => 'border-brand-800 bg-brand-700 text-white'],
    ];
    $info = $map[$status] ?? ['label' => $status ?: 'Tidak diketahui', 'b' => 'border-ink-300 bg-ink-100 text-ink-700'];
    $sizeClass = $size === 'sm' ? 'px-2.5 py-1 text-xs' : 'px-3 py-1.5 text-sm';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border font-semibold $info[b] $sizeClass"]) }}>
    {{ $info['label'] }}
</span>
