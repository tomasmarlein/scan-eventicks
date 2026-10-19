@extends('template.template')

@section('main')
    @php
        $resultStatus = in_array($scan['status'] ?? '', ['success', 'warning'], true) ? $scan['status'] : 'error';
        $resultTitle = $resultStatus === 'success' ? 'Check-in geslaagd' : ($resultStatus === 'warning' ? 'Ticket vraagt aandacht' : 'Ticket niet toegelaten');
    @endphp

    <main class="scan-result-page scan-result-page--{{ $resultStatus }}">
        <div class="scan-result-page__shell">
            <div class="scan-result-page__brand"><i class="fa-solid fa-qrcode" aria-hidden="true"></i> Eventicks scanner</div>
            <section class="scan-result-card" aria-labelledby="resultTitle">
                <span class="scan-result-card__icon" aria-hidden="true">
                    <i class="fa-solid {{ $resultStatus === 'success' ? 'fa-check' : 'fa-exclamation' }}"></i>
                </span>
                <span class="section-kicker">{{ $event->name ?? 'Evenement' }}</span>
                <h1 id="resultTitle">{{ $resultTitle }}</h1>
                <p class="scan-result-card__message">{{ $scan['message'] ?? 'Scan verwerkt.' }}</p>

                @if(!empty($scan['orderline']['name']) || !empty($scan['orderline']['ticket']['name']) || !empty($scan['order_ref']))
                    <dl class="scan-result-card__details">
                        @if(!empty($scan['orderline']['name']))
                            <div><dt>Naam</dt><dd>{{ $scan['orderline']['name'] }}</dd></div>
                        @endif
                        @if(!empty($scan['orderline']['ticket']['name']))
                            <div><dt>Tickettype</dt><dd>{{ $scan['orderline']['ticket']['name'] }}</dd></div>
                        @endif
                        @if(!empty($scan['order_ref']))
                            <div><dt>Order</dt><dd>{{ $scan['order_ref'] }}</dd></div>
                        @endif
                    </dl>
                @endif
            </section>

            <form id="resultForm" method="POST" action="{{ route('scan.camera', [$organisation->slug, $event->slug]) }}">
                @csrf
                <input type="hidden" name="tickets" value='@json($tickets)'>
                <button type="submit" class="app-button app-button--primary"><i class="fa-solid fa-camera" aria-hidden="true"></i> Verder scannen</button>
            </form>
            <p class="scan-result-page__hint">De scanner opent automatisch opnieuw.</p>
        </div>
    </main>
@endsection

@section('script_after')
    <script>
        (() => {
            const form = document.getElementById('resultForm');
            const success = @json($resultStatus === 'success');
            try { if ('vibrate' in navigator) navigator.vibrate(success ? [40, 30, 40] : [80, 50, 80]); } catch (_) {}
            try { new Audio(success ? '/sounds/success.mp3' : '/sounds/error.mp3').play().catch(() => {}); } catch (_) {}
            window.setTimeout(() => form?.requestSubmit(), 3000);
        })();
    </script>
@endsection
