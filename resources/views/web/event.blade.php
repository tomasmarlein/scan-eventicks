@extends('template.template')

@section('main')
    <header class="section bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-5">
                        <a href="{{ route('dashboard') }}" class="text-white text-decoration-none"><i class="fa-solid fa-arrow-left-long me-2 text-white"></i> Terug</a>
                    </div>
                    <h5 class="text-white">{{ $event['name'] }}</h5>
                    <div class="ck-text">
                        {{ $event['address'] }}, {{ $event['postcode'] }} {{ $event['plaats'] }}
                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="section">
        <div class="container">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card bg-white border-1 border-radius-md p-4">
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

                            $to_scan = $sold_tickets - $scanned_tickets;
                        @endphp
                        <div class="row g-4">
                            <div class="col-12">
                                <h5>Overzicht tickets</h5>
                            </div>
                            <div class="col-6">
                                <div class="card text-center p-4">
                                    <h1 class="text-primary">{{ $scanned_tickets }}</h1>
                                    <div class="ck-text text-muted">
                                        Gescande tickets
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card text-center p-4">
                                    <h1>{{ $to_scan }}</h1>
                                    <div class="ck-text text-muted">
                                        Nog te scannen
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card bg-white border-1 border-radius-md p-4">
                        <div class="row g-4">
                            <div class="col-12">
                                <h5>Pet ticket type</h5>
                            </div>
                            @foreach($event['tickets'] as $ticket)
                                @php
                                    $sold = $ticket['verkochte_tickets'];
                                    $scanned = 0;

                                    foreach ($ticket['orderlines'] as $orderline) {
                                        if ($orderline['scanned'] == 1) {
                                            $scanned += 1;
                                        }
                                    }
                                @endphp
                                <div class="col-12">
                                    <div class="card bg-light border-0 border-radius-md p-3">
                                        <div class="row g-4 align-items-center">
                                            <div class="col-12">
                                                <span class="badge badge-name">
                                                    {{ $ticket['name'] }}
                                                </span>
                                            </div>
                                            <div class="col-6">
                                                {{ $sold }} tickets
                                            </div>
                                            <div class="col-6 text-muted text-end">
                                                {{ $scanned }} gescand
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="btn-wrapper">
                        <a href="{{ route('scan.tickets', [$organisation['slug'], $event['slug']]) }}" class="btn btn-primary">
                            <i class="fa-light fa-qrcode-read me-3"></i>
                            Start scanner
                        </a>
                        <a href="{{ route('scan.manuel', [$organisation['slug'], $event['slug']]) }}" class="btn btn-white">
                            <i class="fa-light fa-list-check me-3"></i>
                            Bekijk ticketlijst
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
