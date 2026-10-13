@extends('template.template')

@section('main')
    <header class="scanner-hero">
        <div class="container scanner-shell">
            <a href="{{ route('scan.tickets', [$organisation->slug, $event->slug]) }}" class="scanner-back">
                <i class="fa-solid fa-arrow-left-long" aria-hidden="true"></i> Tickettypes wijzigen
            </a>
            <div class="scanner-hero__eyebrow">{{ $event->name }}</div>
            <h1>Tickets scannen</h1>
            <p>Houd de QR-code van een ticket binnen het kader. De scanner blijft na elke scan actief.</p>
        </div>
    </header>

    <main id="page-scan" class="scanner-page" data-feedback-ms="{{ $feedbackMs ?? 1400 }}" data-state="starting">
        <div class="container scanner-shell">
            <section class="scanner-card" aria-label="Ticketscanner">
                <div class="scanner-card__heading">
                    <div>
                        <span class="scanner-card__label">CAMERA</span>
                        <h2>Scan een QR-code</h2>
                    </div>
                    <span class="scanner-live"><span class="scanner-live__dot"></span> Live</span>
                </div>

                <div class="scan-stage">
                    <video id="preview" autoplay muted playsinline aria-label="Live camerabeeld"></video>
                    <div class="scan-stage__shade" aria-hidden="true"></div>
                    <div class="scan-stage__guide" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                    <button id="torchBtn" type="button" class="scan-stage__torch" disabled aria-label="Zaklamp inschakelen" aria-pressed="false">
                        <i class="fa-solid fa-bolt" aria-hidden="true"></i> Zaklamp
                    </button>
                    <span class="scan-stage__caption">Plaats de QR-code in het kader</span>
                </div>

                <div class="scanner-status" role="status" aria-live="polite">
                    <span class="scanner-status__icon" aria-hidden="true"><i class="fa-solid fa-qrcode"></i></span>
                    <div>
                        <span class="scanner-status__label">SCANSTATUS</span>
                        <p id="status">Camera starten…</p>
                        <button id="retryBtn" type="button" class="scanner-retry d-none">Opnieuw proberen</button>
                    </div>
                </div>
            </section>

            <a class="scanner-manual" href="{{ route('scan.manuel', [$organisation->slug, $event->slug]) }}">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                Ticket handmatig opzoeken
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>

            <form id="resultForm" class="d-none" method="POST"
                  action="{{ route('scan.result', [$organisation->slug, $event->slug]) }}">
                @csrf
                <input type="hidden" name="qr" id="qrField">
                <input type="hidden" name="tickets" value='@json($tickets)'>
            </form>
        </div>
    </main>

    <div class="scan-feedback" id="scanFeedback" role="status" aria-live="polite" data-status="idle">
        <span class="scan-feedback__symbol" id="scanFeedbackSymbol" aria-hidden="true"></span>
        <div class="scan-feedback__content">
            <p class="scan-feedback__title" id="scanFeedbackTitle">Scan verwerkt</p>
            <p class="scan-feedback__message" id="scanFeedbackMessage"></p>
            <div class="scan-feedback__meta">
                <span id="scanFeedbackName"></span>
                <span id="scanFeedbackTicket"></span>
                <span id="scanFeedbackOrder"></span>
            </div>
        </div>
    </div>

    @vite(['resources/js/shared/scan.js'])
@endsection
