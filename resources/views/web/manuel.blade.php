@extends('template.template')

@section('main')
    @include('web.partials.page-header', [
        'backUrl' => route('event.overview', [$organisation->slug, $event->slug]),
        'backLabel' => 'Evenement',
        'eyebrow' => $event->name,
        'title' => 'Tickets opzoeken',
        'description' => 'Zoek een ticket en controleer de toegang handmatig.',
    ])

    <main class="app-main">
        <div class="container app-shell">
            <section class="surface-card search-panel" data-search-url="{{ route('events.tickets.search', [$organisation->slug, $event->slug]) }}" aria-labelledby="searchTitle">
                <div class="search-panel__heading">
                    <div><span class="section-kicker">HANDMATIGE CONTROLE</span><h2 id="searchTitle">Zoek een ticket</h2></div>
                    @if($event->tickets->isNotEmpty())
                        <a href="{{ route('scan.tickets', [$organisation->slug, $event->slug]) }}" class="app-button app-button--secondary">
                            <i class="fa-solid fa-camera" aria-hidden="true"></i> Scanner openen
                        </a>
                    @endif
                </div>
                <label class="search-field" for="ticketSearch">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" id="ticketSearch" autocomplete="off" placeholder="Naam, QR-code of ordernummer" aria-describedby="searchHint">
                </label>
                <p id="searchHint" class="search-panel__hint">Zoek ook op e-mail of met een deel van de QR-code, zoals or_.</p>
                @if($hasMoreOrderlines ?? false)
                    <p class="search-panel__hint">De {{ $manualLimit }} recentste tickets worden getoond. Zoek om andere tickets te vinden.</p>
                @endif
            </section>

            <div class="page-section-heading app-section-space">
                <div><span class="section-kicker">TICKETS</span><h2>Resultaten</h2></div>
                <span class="count-pill" id="resultCount">{{ count($orderlines) }} {{ count($orderlines) === 1 ? 'ticket' : 'tickets' }}</span>
            </div>

            <div id="searchSpinner" class="search-loading d-none" role="status"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Zoeken…</div>
            <p id="searchError" class="alert alert-danger d-none" role="alert">Zoeken is tijdelijk niet beschikbaar. Probeer opnieuw.</p>
            <div class="ticket-list" id="orderlinesList" aria-live="polite">
                @include('web.partials.orderlines-list', ['orderlines' => $orderlines, 'event' => $event])
            </div>
            <p id="limitedState" class="list-hint d-none"></p>
        </div>
    </main>

@endsection
