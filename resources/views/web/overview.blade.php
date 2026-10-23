@extends('template.template')

@section('main')
    @include('web.partials.page-header', [
        'backUrl' => route('dashboard'),
        'backLabel' => 'Organisaties',
        'eyebrow' => 'ORGANISATIE',
        'title' => $organisation->name,
        'description' => 'Kies een evenement om de toegang te beheren.',
        'flat' => true,
    ])

    <main class="app-main app-main--flat">
        <div class="container app-shell">
            @if($events->isNotEmpty())
                <div class="surface-card organisation-filter" data-list-search data-search-url="{{ route('organisation.events.search', $organisation->slug) }}" data-list-id="eventList" data-count-id="eventCount" data-empty-id="eventSearchEmpty" data-singular="evenement" data-plural="evenementen">
                    <label class="search-field" for="eventSearch">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input id="eventSearch" type="search" placeholder="Zoek een evenement" autocomplete="off" maxlength="100" aria-controls="eventList">
                    </label>
                    <p class="organisation-filter__feedback search-loading d-none" role="status">Evenementen zoeken...</p>
                    <p class="organisation-filter__feedback organisation-filter__feedback--error search-error d-none" role="alert">Zoeken is niet gelukt. Probeer het opnieuw.</p>
                </div>
            @endif

            <div class="page-section-heading {{ $events->isNotEmpty() ? 'app-section-space' : '' }}">
                <div><span class="section-kicker">EVENEMENTEN</span><h2>Overzicht</h2></div>
                <span class="count-pill" id="eventCount" aria-live="polite">{{ $events->count() }} {{ $events->count() === 1 ? 'evenement' : 'evenementen' }}</span>
            </div>

            <div class="event-list" id="eventList">
                @include('web.partials.event-list', ['showEmptyState' => true])
            </div>
            <p class="surface-card empty-state d-none" id="eventSearchEmpty">Geen evenementen gevonden voor deze zoekopdracht.</p>
        </div>
    </main>
@endsection
