@extends('template.template')

@section('main')
    <style>
        /* Overlays zorgen niet voor extra document-hoogte */
        #scan-controls {
            position        : absolute;
            top             : 0;
            right           : 0;
            display         : flex;
            flex-direction  : row;
            align-items     : center;
            justify-content : space-between;
            gap             : .5rem;
            z-index         : 10;
            padding         : 1rem;
        }

        #status {
            margin  : 0;
            display : none;
        }
    </style>

    <header class="section bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-5">
                        <a href="{{ route('scan.tickets', $event['uuid']) }}" class="text-white text-decoration-none">
                            <i class="fa-solid fa-arrow-left-long me-2 text-white"></i> Terug</a>
                    </div>
                    <h5 class="text-white">Scanner actief</h5>
                    <div class="ck-text">
                        scan een QR-code
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="page-scan" class="section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card bg-white border-1 border-radius-md p-4">
                        <div class="position-relative">
                            <div id="scan-controls">
                                <select id="cameraSelect" class="form-select d-none" style="max-width: 420px"></select>
                                <button id="startBtn" class="btn btn-primary d-none">Start</button>
                                <button id="stopBtn" class="btn btn-outline-secondary d-none" disabled>Stop</button>
                                <button id="torchBtn" class="btn btn-white btn-round" disabled>🔦</button>
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
                              action="{{ route('scan.result', $event['uuid']) }}">
                            @method('POST')
                            @csrf
                            <input type="hidden" name="qr" id="qrField">

                            <input type="hidden" name="tickets" value='@json($tickets)'>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @vite(['resources/js/shared/scan.js'])
@endsection
