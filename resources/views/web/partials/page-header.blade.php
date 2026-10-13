<header class="app-hero {{ ($flat ?? false) ? 'app-hero--flat' : '' }}">
    <div class="container app-shell">
        @isset($backUrl)
            <a class="app-hero__back" href="{{ $backUrl }}">
                <i class="fa-solid fa-arrow-left-long" aria-hidden="true"></i>
                {{ $backLabel ?? 'Terug' }}
            </a>
        @endisset
        <p class="app-hero__eyebrow">{{ $eyebrow ?? 'Eventicks scanner' }}</p>
        <h1>{{ $title }}</h1>
        @isset($description)
            <p class="app-hero__description">{{ $description }}</p>
        @endisset
    </div>
</header>
