@extends('template.template')

@section('main')
    <main id="page-scan" class="scanner-page" data-feedback-ms="{{ $feedbackMs ?? 1400 }}" data-state="starting">
        <div class="scan-stage">
            <video id="preview" autoplay muted playsinline aria-label="Live camerabeeld"></video>
            <div class="scan-stage__shade" aria-hidden="true"></div>
            <div class="scan-stage__guide" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
            <span class="scan-stage__caption">Plaats de QR-code in het kader</span>
        </div>

        <div class="scanner-screen">
            <header class="scanner-topbar">
                <a href="{{ route('scan.tickets', [$organisation->slug, $event->slug]) }}" class="scanner-back" aria-label="Terug naar tickettypes">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
                <div class="scanner-topbar__title">
                    <span>{{ $event->name }}</span>
                    <h1>Tickets scannen</h1>
                </div>
                <span class="scanner-live"><span class="scanner-live__dot"></span> Live</span>
            </header>

            <div class="scanner-screen__space" aria-hidden="true"></div>

            <div class="scanner-controls">
                <div class="scanner-status" role="status" aria-live="polite">
                    <span class="scanner-status__icon" aria-hidden="true"><i class="fa-solid fa-qrcode"></i></span>
                    <div>
                        <p id="status">Camera starten…</p>
                        <button id="retryBtn" type="button" class="scanner-retry d-none">Opnieuw proberen</button>
                    </div>
                </div>
                <div class="scanner-controls__actions">
                    <a class="scanner-manual" href="{{ route('scan.manuel', [$organisation->slug, $event->slug]) }}">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Ticket opzoeken
                    </a>
                    <button id="torchBtn" type="button" class="scanner-torch" disabled aria-label="Zaklamp inschakelen" aria-pressed="false">
                        <i class="fa-solid fa-bolt" aria-hidden="true"></i> Zaklamp
                    </button>
                </div>
            </div>

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
