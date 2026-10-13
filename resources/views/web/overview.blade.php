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
            <div class="page-section-heading">
                <div><span class="section-kicker">EVENEMENTEN</span><h2>Overzicht</h2></div>
                <span class="count-pill">{{ $events->count() }} {{ $events->count() === 1 ? 'evenement' : 'evenementen' }}</span>
            </div>

            <div class="event-list">
                @forelse($events as $event)
                    @php
                        $soldTickets = (int) ($event->sold_tickets ?? 0);
                        $scannedTickets = (int) ($event->scanned_tickets ?? 0);
                        $percent = min(100, max(0, (float) ($event->scan_percentage ?? 0)));
                    @endphp
                    <a href="{{ route('event.overview', [$organisation->slug, $event->slug]) }}" class="event-overview-card">
                        <div class="event-overview-card__top">
                            <div>
                                <span class="section-kicker">EVENEMENT</span>
                                <h3>{{ $event->name }}</h3>
                            </div>
                            <span class="event-overview-card__arrow"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                        </div>
                        <div class="event-overview-card__details">
                            <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ $event->start ? \Carbon\Carbon::parse($event->start)->translatedFormat('d F Y') : 'Datum onbekend' }}</span>
                            @if($event->address || $event->plaats)
                                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ collect([$event->address, trim(($event->postcode ?? '').' '.($event->plaats ?? ''))])->filter()->implode(', ') }}</span>
                            @endif
                        </div>
                        <div class="event-overview-card__progress">
                            <div><strong>{{ $scannedTickets }} / {{ $soldTickets }}</strong><span>tickets gescand</span></div>
                            <span>{{ number_format($percent, 0) }}%</span>
                        </div>
                        <div class="progress" role="progressbar" aria-label="Gescande tickets voor {{ $event->name }}" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: {{ $percent }}%"></div>
                        </div>
                    </a>
                @empty
                    <div class="surface-card empty-state">
                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                        <h2>Geen evenementen gevonden</h2>
                        <p>Er zijn nog geen evenementen gekoppeld aan deze organisatie.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </main>
@endsection
