@extends('template.template')

@section('main')
    @include('web.partials.page-header', [
        'backUrl' => route('event.overview', [$organisation->slug, $event->slug]),
        'backLabel' => 'Evenement',
        'eyebrow' => $event->name,
        'title' => 'Tickettypes kiezen',
        'description' => 'Bepaal welke tickets deze scanner accepteert.',
    ])

    <main class="app-main">
        <div class="container app-shell app-shell--narrow">
            <form method="POST" action="{{ route('scan.camera', [$organisation->slug, $event->slug]) }}" class="surface-card ticket-setup">
                @csrf
                <div class="ticket-setup__heading">
                    <span class="section-kicker">SCANNER INSTELLEN</span>
                    <h2>Te scannen tickets</h2>
                    <p>Alle tickettypes zijn standaard geselecteerd.</p>
                </div>

                @if ($errors->has('tickets'))
                    <div class="alert alert-danger" role="alert">{{ $errors->first('tickets') }}</div>
                @endif

                <div class="ticket-options">
                    @foreach($event->tickets as $ticket)
                        <label class="ticket-option" for="ticket-{{ $ticket->id }}">
                            <span class="ticket-option__icon"><i class="fa-solid fa-ticket" aria-hidden="true"></i></span>
                            <span class="ticket-option__name">{{ $ticket->name }}</span>
                            <input class="form-check-input" type="checkbox" id="ticket-{{ $ticket->id }}" value="{{ $ticket->id }}" name="tickets[]" checked>
                        </label>
                    @endforeach
                </div>

                <div class="ticket-setup__footer">
                    <button type="submit" class="app-button app-button--primary" @if($event->tickets->isEmpty()) disabled @endif>
                        <i class="fa-solid fa-camera" aria-hidden="true"></i> Scanner openen
                    </button>
                </div>
            </form>
        </div>
    </main>
@endsection
