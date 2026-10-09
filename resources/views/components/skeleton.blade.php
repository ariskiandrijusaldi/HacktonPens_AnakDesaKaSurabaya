@props(['rows' => 3])

<div {{ $attributes->merge(['class' => 'space-y-3']) }} role="status" aria-live="polite" aria-busy="true">
    <span class="sr-only">Memuat data…</span>
    @for ($i = 0; $i < $rows; $i++)
        <div class="skeleton h-16 w-full"></div>
    @endfor
</div>
