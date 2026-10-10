@props(['eyebrow' => 'Ruang Petugas', 'title', 'description' => null])

<div class="-mx-4 -mt-8 sm:-mx-6 lg:-mx-8">
    <header class="band-dark px-4 py-10 text-white sm:px-6 sm:py-12 lg:px-8">
        <div class="mx-auto flex max-w-6xl flex-col gap-3">
            @if ($eyebrow)
                <span class="of-eyebrow text-white/80">{{ $eyebrow }}</span>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-bold leading-tight sm:text-4xl">{{ $title }}</h1>
                {{ $slot }}
            </div>

            @if ($description)
                <p class="max-w-2xl text-base leading-relaxed text-white/85">{{ $description }}</p>
            @endif
        </div>
    </header>
</div>
