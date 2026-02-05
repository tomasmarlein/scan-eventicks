@extends('template.template')

@section('main')
    <header class="section bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-5">
                        <a href="{{ route('event.overview', [$organisation['slug'], $event['slug']]) }}" class="text-white text-decoration-none">
                            <i class="fa-solid fa-arrow-left-long me-2 text-white"></i> Terug</a>
                    </div>
                    <h5 class="text-white">Scanner instellen</h5>
                    <div class="ck-text">
                        Selecteer welke ticket types je wilt scannen
                    </div>
                </div>
            </div>
        </div>
    </header>

    <form method="post" action="{{ route('scan.camera', [$organisation['slug'], $event['slug']]) }}" class="w-100">
        @csrf
        <section class="section">
            <div class="container">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card bg-white p-4 border-1 border-radius-lg">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h5>Type tickets</h5>
                                </div>
                            </div>
                            <div class="row g-4">
                                @foreach($event['tickets'] as $ticket)
                                    <div class="col-12">
                                        <div class="card bg-light border-0 p-3 border-radius-md">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="ticket-{{ $ticket['name'] }}" value="{{ $ticket['id'] }}" name="tickets[]" checked>
                                                <label class="form-check-label" for="ticket-{{ $ticket['name'] }}">{{ $ticket['name'] }}</label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="btn-wrapper">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-light fa-qrcode-read me-3"></i> Start scanner
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
@endsection
