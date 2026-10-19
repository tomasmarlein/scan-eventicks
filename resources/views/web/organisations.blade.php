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
                <div class="surface-card organisation-filter">
                    <label class="search-field" for="organisationSearch">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input id="organisationSearch" type="search" placeholder="Zoek een organisatie" autocomplete="off">
                    </label>
                </div>
            @endif

            <div class="page-section-heading app-section-space">
                <div>
                    <span class="section-kicker">WERKRUIMTE</span>
                    <h2>Jouw organisaties</h2>
                </div>
                <span class="count-pill" id="organisationCount">{{ $organisations->count() }} {{ $organisations->count() === 1 ? 'organisatie' : 'organisaties' }}</span>
            </div>

            <div class="entity-list">
                @forelse($organisations as $organisation)
                    <a href="{{ route('organisation.overview', $organisation['slug']) }}" class="entity-card">
                        <span class="entity-card__icon"><i class="fa-solid fa-building" aria-hidden="true"></i></span>
                        <span class="entity-card__content"><strong>{{ $organisation['name'] }}</strong><small>Evenementen bekijken</small></span>
                        <i class="fa-solid fa-arrow-right entity-card__arrow" aria-hidden="true"></i>
                    </a>
                @empty
                    <div class="surface-card empty-state">
                        <i class="fa-solid fa-building-circle-exclamation" aria-hidden="true"></i>
                        <h2>Geen organisaties gevonden</h2>
                        <p>Je hebt momenteel geen toegang tot een organisatie.</p>
                    </div>
                @endforelse
            </div>
            <p class="surface-card empty-state d-none" id="organisationSearchEmpty">Geen organisaties gevonden voor deze zoekopdracht.</p>
        </div>
    </main>
@endsection
