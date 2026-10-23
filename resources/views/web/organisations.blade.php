@extends('template.template')

@section('main')
    @include('web.partials.page-header', [
        'eyebrow' => 'STARTPUNT',
        'title' => 'Organisaties',
        'description' => 'Kies de organisatie waarvoor je tickets wilt scannen.',
        'flat' => $organisations->isEmpty(),
    ])

    <main class="app-main {{ $organisations->isEmpty() ? 'app-main--flat' : '' }}">
        <div class="container app-shell">
            @if($organisations->isNotEmpty())
                <div class="surface-card organisation-filter" data-list-search data-search-url="{{ route('organisations.search') }}" data-list-id="organisationList" data-count-id="organisationCount" data-empty-id="organisationSearchEmpty" data-singular="organisatie" data-plural="organisaties">
                    <label class="search-field" for="organisationSearch">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input id="organisationSearch" type="search" placeholder="Zoek een organisatie" autocomplete="off" maxlength="100" aria-controls="organisationList">
                    </label>
                    <p class="organisation-filter__feedback search-loading d-none" role="status">Organisaties zoeken...</p>
                    <p class="organisation-filter__feedback organisation-filter__feedback--error search-error d-none" role="alert">Zoeken is niet gelukt. Probeer het opnieuw.</p>
                </div>
            @endif

            <div class="page-section-heading app-section-space">
                <div>
                    <span class="section-kicker">WERKRUIMTE</span>
                    <h2>Jouw organisaties</h2>
                </div>
                <span class="count-pill" id="organisationCount" aria-live="polite">{{ $organisations->count() }} {{ $organisations->count() === 1 ? 'organisatie' : 'organisaties' }}</span>
            </div>

            <div class="entity-list" id="organisationList">
                @include('web.partials.organisation-list', ['showEmptyState' => true])
            </div>
            <p class="surface-card empty-state d-none" id="organisationSearchEmpty">Geen organisaties gevonden voor deze zoekopdracht.</p>
        </div>
    </main>
@endsection
