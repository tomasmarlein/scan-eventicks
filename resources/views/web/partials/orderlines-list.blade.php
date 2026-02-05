@foreach($orderlines as $orderline)
    <div class="col-12">
        <div class="card border-1 bg-white p-3 border-radius-lg">
            <div class="row align-items-center">
                <div class="col-8">
                    <h3>{{ $orderline['unique_qr_id'] }}</h3>
                    <div class="mb-3">
                        <span class="text-muted">{{ $orderline['name'] }}</span>
                    </div>
                    <div>
                        @if($orderline['blocked'])
                            <span class="badge bg-danger-subtle text-danger">
                            Geblokkeerd
                        </span>
                        @elseif($orderline['scanned'])
                            <span class="badge bg-dark-subtle text-dark">
                            Gescand
                        </span>
                        @else
                            <span class="badge bg-primary-subtle text-primary">
                            Geldig
                        </span>
                        @endif
                    </div>
                </div>
                <div class="col-4 text-center">
                    @if($orderline['blocked'])
                        <i class="fa-light fa-circle-xmark text-danger" style="font-size: 1.5rem;"></i>
                    @elseif($orderline['scanned'])
                        <i class="fa-kit fa-light-ticket-circle-check text-dark" style="font-size: 1.5rem;"></i>
                    @else
                        <a class="btn btn-primary" title="Valideren"
                           href="{{ $orderline['url_checkin'] ?? '#' }}"
                           style="padding: .5rem 1rem;">
                            Check-in
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
