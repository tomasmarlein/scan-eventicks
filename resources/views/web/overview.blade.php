@extends('template.template')

@section('main')
    <header class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h3 class="text-primary">{{ $organisation['name'] }}</h3>
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
                @foreach($events as $event)
                    @php
                        $sold_tickets = 0;
                        $scanned_tickets = 0;

                        foreach ($event['tickets'] as $ticket) {
                            $sold_tickets += $ticket['verkochte_tickets'];

                            foreach ($ticket['orderlines'] as $orderline) {
                                if ($orderline['scanned'] == 1) {
                                    $scanned_tickets += 1;
                                }
                            }
                        }
                    @endphp
                    <div class="col-12">
                        <div class="event-card card p-4 bg-white border-radius-lg border-1">
                            <h6 class="mb-3">{{ $event['name'] }}</h6>
                            <div class="event_date text-muted mb-2">
                                <i class="fa-light fa-calendar-days me-2"></i>
                                {{ \Carbon\Carbon::parse($event['start'])->translatedFormat('d F Y') }}
                            </div>
                            <div class="event_location text-muted mb-2">
                                <i class="fa-light fa-location-dot me-2"></i>
                                {{ $event['address'] }}, {{ $event['postcode'] }} {{ $event['plaats'] }}
                            </div>
                            <div class="event_tickets text-muted mb-5 d-flex flex-column gap-1">
                                <div class="mb-3">
                                    <i class="fa-light fa-ticket me-2"></i>
                                    {{ $scanned_tickets }} / {{ $sold_tickets }} tickets gescand
                                </div>
                                <div>
                                    @php
                                        // calculate percentage
                                        $percent = $sold_tickets > 0 ? ($scanned_tickets / $sold_tickets) * 100 : 0;
                                    @endphp

                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="{{ $sold_tickets }}"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="btn-wrapper">
                                <a href="{{ route('event.overview', [$organisation['slug'], $event['slug']]) }}" class="btn btn-primary">
                                    Bekijk tickets
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
