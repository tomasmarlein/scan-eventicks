@extends('template.template')

@section('main')
    <style>
        #scan-controls {
            position: absolute;
            top: 0;
            right: 0;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            z-index: 10;
            padding: 1rem;
        }

        #status {
            margin: 0;
            display: none;
        }

        .scan-feedback {
            position: fixed;
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
            z-index: 1050;
            display: none;
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, .16);
            background: #121212;
            color: #fff;
            box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, .35);
            overflow: hidden;
        }

        .scan-feedback.is-visible {
            display: block;
        }

        .scan-feedback__bar {
            height: .35rem;
            background: #6c757d;
        }

        .scan-feedback[data-status="success"] .scan-feedback__bar { background: #2ecc71; }
        .scan-feedback[data-status="warning"] .scan-feedback__bar { background: #f39c12; }
        .scan-feedback[data-status="error"] .scan-feedback__bar { background: #e74c3c; }

        .scan-feedback__body {
            padding: 1rem;
        }

        .scan-feedback__title {
            margin: 0 0 .25rem;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .scan-feedback__meta {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .75rem;
        }

        .scan-feedback__chip {
            display: inline-flex;
            border-radius: 999px;
            border: 1px solid #2d2d2d;
            background: #1d1d1d;
            color: #bbb;
            padding: .2rem .55rem;
            font-size: .8rem;
        }
    </style>

    <header class="section bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-5">
                        <a href="{{ route('scan.tickets', [$organisation->slug, $event->slug]) }}" class="text-white text-decoration-none">
                            <i class="fa-solid fa-arrow-left-long me-2 text-white"></i> Terug</a>
                    </div>
                    <h5 class="text-white">Scanner actief</h5>
                    <div class="ck-text">
                        Scan een QR-code. De camera blijft actief na elke scan.
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="page-scan" class="section" data-feedback-ms="{{ $feedbackMs ?? 1400 }}">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card bg-white border-1 border-radius-md p-4">
                        <div class="position-relative">
                            <div id="scan-controls">
                                <select id="cameraSelect" class="form-select d-none" style="max-width: 420px"></select>
                                <button id="startBtn" type="button" class="btn btn-primary d-none">Start</button>
                                <button id="stopBtn" type="button" class="btn btn-outline-secondary d-none" disabled>Stop</button>
                                <button id="torchBtn" type="button" class="btn btn-white btn-round" disabled aria-label="Zaklamp">🔦</button>
                            </div>
                        </div>
                        <div class="scan-wrap">
                            <div class="ratio ratio-1x1 w-100 border-radius-md">
                                <video id="preview" autoplay muted playsinline
                                       style="width:100%; height: 100%; object-fit:cover;"></video>
                            </div>

                            <p class="text-light" id="status">Status: klaar</p>
                        </div>

                        <form id="resultForm" class="d-none" method="POST"
                              action="{{ route('scan.result', [$organisation->slug, $event->slug]) }}">
                            @csrf
                            <input type="hidden" name="qr" id="qrField">
                            <input type="hidden" name="tickets" value='@json($tickets)'>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="scan-feedback" id="scanFeedback" role="status" aria-live="polite" data-status="idle">
        <div class="scan-feedback__bar"></div>
        <div class="scan-feedback__body">
            <p class="scan-feedback__title" id="scanFeedbackTitle">Scan verwerkt</p>
            <div id="scanFeedbackMessage" class="text-secondary small"></div>
            <div class="scan-feedback__meta">
                <span class="scan-feedback__chip" id="scanFeedbackName">Naam: -</span>
                <span class="scan-feedback__chip" id="scanFeedbackTicket">Ticket: -</span>
                <span class="scan-feedback__chip" id="scanFeedbackOrder">Order: -</span>
            </div>
        </div>
    </div>

    @vite(['resources/js/shared/scan.js'])
@endsection
