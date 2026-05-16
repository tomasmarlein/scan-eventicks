@extends('template.template')

@section('main')
    <header class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h3 class="text-primary">{{ $organisation->name }}</h3>
                    <div class="ck-text text-muted">
                        Selecteer een evenement om tickets te beheren
                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="section pt-0">
        <div class="container">
            <div class="row g-4">
                @forelse($events as $event)
                    @php
                        $soldTickets = (int) ($event->sold_tickets ?? 0);
                        $scannedTickets = (int) ($event->scanned_tickets ?? 0);
                        $percent = (float) ($event->scan_percentage ?? 0);
                    @endphp
                    <div class="col-12">
                        <div class="event-card card p-4 bg-white border-radius-lg border-1">
                            <h6 class="mb-3">{{ $event->name }}</h6>
                            <div class="event_date text-muted mb-2">
                                <i class="fa-light fa-calendar-days me-2"></i>
                                {{ $event->start ? \Carbon\Carbon::parse($event->start)->translatedFormat('d F Y') : 'Datum onbekend' }}
                            </div>
                            <div class="event_location text-muted mb-2">
                                <i class="fa-light fa-location-dot me-2"></i>
                                {{ $event->address }}, {{ $event->postcode }} {{ $event->plaats }}
                            </div>
                            <div class="event_tickets text-muted mb-5 d-flex flex-column gap-1">
                                <div class="mb-3">
                                    <i class="fa-light fa-ticket me-2"></i>
                                    {{ $scannedTickets }} / {{ $soldTickets }} tickets gescand
                                </div>
                                <div>
                                    <div class="progress" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar bg-primary" style="width: {{ min(100, max(0, $percent)) }}%;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="btn-wrapper">
                                <a href="{{ route('event.overview', [$organisation->slug, $event->slug]) }}" class="btn btn-primary">
                                    Bekijk tickets
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card p-4 bg-white border-radius-lg border-1 text-center text-muted">
                            Geen evenementen gevonden.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
