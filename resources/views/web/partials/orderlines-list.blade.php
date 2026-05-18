@forelse($orderlines as $orderline)
    <div class="col-12">
        <div class="card border-1 bg-white p-3 border-radius-lg">
            <div class="row align-items-center g-3">
                <div class="col-8">
                    <h3 class="h6 mb-2">{{ $orderline['unique_qr_id'] ?? 'Onbekende QR' }}</h3>
                    <div class="mb-3">
                        <span class="text-muted">{{ $orderline['name'] ?? 'Naam onbekend' }}</span>
                    </div>
                    <div>
                        @if(!empty($orderline['blocked']))
                            <span class="badge bg-danger-subtle text-danger">Geblokkeerd</span>
                        @elseif(!empty($orderline['scanned']))
                            <span class="badge bg-dark-subtle text-dark">Gescand</span>
                        @else
                            <span class="badge bg-primary-subtle text-primary">Geldig</span>
                        @endif
                    </div>
                </div>
                <div class="col-4 text-center">
                    @if(!empty($orderline['blocked']))
                        <i class="fa-light fa-circle-xmark text-danger" style="font-size: 1.5rem;"></i>
                    @elseif(!empty($orderline['scanned']))
                        <form method="POST" action="{{ $orderline['url_checkout'] ?? '#' }}" class="d-inline">
                            @csrf
                            <button class="btn btn-white" type="submit" style="padding: .5rem 1rem;">Check-uit</button>
                        </form>
                    @elseif(!empty($orderline['url_checkin']))
                        <form method="POST" action="{{ $orderline['url_checkin'] }}" class="d-inline">
                            @csrf
                            <button class="btn btn-primary" type="submit" style="padding: .5rem 1rem;">Check-in</button>
                        </form>
                    @else
                        <span class="text-muted small">Geen actie</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="card border-1 bg-white p-4 border-radius-lg text-center text-muted">
            Geen tickets gevonden.
        </div>
    </div>
@endforelse
