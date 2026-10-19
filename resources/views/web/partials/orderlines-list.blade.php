@forelse($orderlines as $orderline)
    @php
        $ticketStatus = !empty($orderline['blocked']) ? 'blocked' : (!empty($orderline['scanned']) ? 'scanned' : 'valid');
    @endphp
    <article class="ticket-row" aria-label="Ticket van {{ $orderline['name'] ?? 'onbekende naam' }}">
        <span class="ticket-row__icon ticket-row__icon--{{ $ticketStatus }}"><i class="fa-solid fa-ticket" aria-hidden="true"></i></span>
        <div class="ticket-row__info">
            <strong>{{ $orderline['name'] ?? 'Naam onbekend' }}</strong>
            <small title="{{ $orderline['unique_qr_id'] ?? 'Onbekende QR' }}">{{ $orderline['unique_qr_id'] ?? 'Onbekende QR' }}</small>
        </div>
        <span class="status-pill status-pill--{{ $ticketStatus }}">
            {{ $ticketStatus === 'blocked' ? 'Geblokkeerd' : ($ticketStatus === 'scanned' ? 'Ingecheckt' : 'Geldig') }}
        </span>
        <div class="ticket-row__action">
            @if($ticketStatus === 'scanned' && !empty($orderline['url_checkout']))
                <form method="POST" action="{{ $orderline['url_checkout'] }}">
                    @csrf
                    <button class="app-button app-button--secondary" type="submit">Check-uit</button>
                </form>
            @elseif($ticketStatus === 'valid' && !empty($orderline['url_checkin']))
                <form method="POST" action="{{ $orderline['url_checkin'] }}">
                    @csrf
                    <button class="app-button app-button--primary" type="submit">Check-in</button>
                </form>
            @endif
        </div>
    </article>
@empty
    <div class="surface-card empty-state">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <h2>Geen tickets gevonden</h2>
        <p>Probeer een andere naam, QR-code of referentie.</p>
    </div>
@endforelse
