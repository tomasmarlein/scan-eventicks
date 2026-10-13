@extends('template.template')

@section('main')
    @php
        $soldTickets = (int) ($event->sold_tickets ?? 0);
        $scannedTickets = (int) ($event->scanned_tickets ?? 0);
        $toScan = (int) ($event->to_scan_tickets ?? max(0, $soldTickets - $scannedTickets));
        $scanPercent = $soldTickets > 0 ? min(100, round($scannedTickets / $soldTickets * 100)) : 0;
    @endphp

    @include('web.partials.page-header', [
        'backUrl' => route('organisation.overview', $organisation->slug),
        'backLabel' => 'Evenementen',
        'eyebrow' => $organisation->name,
        'title' => $event->name,
        'description' => collect([$event->address, trim(($event->postcode ?? '').' '.($event->plaats ?? ''))])->filter()->implode(', '),
    ])

    <main class="app-main">
        <div class="container app-shell">
            <section class="surface-card quick-start" aria-labelledby="quickStartTitle">
                <div class="quick-start__icon"><i class="fa-solid fa-qrcode" aria-hidden="true"></i></div>
                <div class="quick-start__body">
                    <span class="section-kicker">TOEGANGSCONTROLE</span>
                    <h2 id="quickStartTitle">Klaar om te scannen?</h2>
                    <p>Kies eerst welke tickettypes je wilt scannen.</p>
                </div>
                @if($event->tickets->isNotEmpty())
                    <a href="{{ route('scan.tickets', [$organisation->slug, $event->slug]) }}" class="app-button app-button--primary">
                        <i class="fa-solid fa-camera" aria-hidden="true"></i> Start scanner
                    </a>
                @else
                    <span class="text-muted">Geen tickettypes beschikbaar</span>
                @endif
            </section>

            <div class="page-section-heading app-section-space">
                <div><span class="section-kicker">LIVE OVERZICHT</span><h2>Ticketstatus</h2></div>
            </div>
            <section class="metric-grid" aria-label="Ticketstatus">
                <div class="metric-card"><span>Gescand</span><strong>{{ $scannedTickets }}</strong><small>toegelaten bezoekers</small></div>
                <div class="metric-card"><span>Openstaand</span><strong>{{ $toScan }}</strong><small>nog te scannen</small></div>
            </section>

            <section class="surface-card ticket-breakdown" aria-labelledby="ticketBreakdownTitle">
                <div class="ticket-breakdown__heading">
                    <div><span class="section-kicker">VOORTGANG</span><h2 id="ticketBreakdownTitle">Per tickettype</h2></div>
                    <strong>{{ $scanPercent }}%</strong>
                </div>
                <div class="progress" role="progressbar" aria-label="Totale voortgang" aria-valuenow="{{ $scanPercent }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar" style="width: {{ $scanPercent }}%"></div>
                </div>
                <div class="ticket-breakdown__rows">
                    @forelse($event->tickets as $ticket)
                        @php
                            $sold = (int) ($ticket->sold_tickets ?? $ticket->verkochte_tickets ?? 0);
                            $scanned = (int) ($ticket->scanned_tickets ?? 0);
                        @endphp
                        <div class="ticket-breakdown__row">
                            <span>{{ $ticket->name }}</span>
                            <strong>{{ $scanned }} <span>/ {{ $sold }}</span></strong>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Geen tickettypes gevonden.</p>
                    @endforelse
                </div>
            </section>

            <div class="secondary-actions">
                <a href="{{ route('scan.tickets', [$organisation->slug, $event->slug]) }}" class="app-button app-button--secondary"><i class="fa-solid fa-sliders" aria-hidden="true"></i> Tickettypes kiezen</a>
                <a href="{{ route('scan.manuel', [$organisation->slug, $event->slug]) }}" class="app-button app-button--secondary"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Ticket handmatig opzoeken</a>
            </div>
        </div>
    </main>
@endsection
