@extends('template.template')

@section('main')
    @php
        $soldTickets = (int) $event->tickets->sum(fn ($ticket) => (int) ($ticket->verkochte_tickets ?? 0));
    @endphp

    <header class="section bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-5">
                        <a href="{{ route('event.overview', [$organisation->slug, $event->slug]) }}" class="text-white text-decoration-none">
                            <i class="fa-solid fa-arrow-left-long me-2 text-white"></i> Terug</a>
                    </div>
                    <h5 class="text-white">{{ $event->name }}</h5>
                    <div class="ck-text">
                        {{ $soldTickets }} tickets
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="section"
             data-search-url="{{ route('events.tickets.search', [$organisation->slug, $event->slug]) }}">
        <div class="container">
            @if($hasMoreOrderlines ?? false)
                <div class="alert alert-info">
                    De lijst toont de laatste {{ $manualLimit }} tickets. Gebruik de zoekfunctie om gericht op naam, QR, e-mail of referentie te zoeken.
                </div>
            @endif

            <div class="row g-4 mb-3">
                <div class="col-12">
                    <div class="form-floating mb-3">
                        <input type="search" class="form-control p-4 bg-gray border-1" id="ticketSearch"
                               autocomplete="off"
                               placeholder="Zoek op naam, QR, e-mail, referentie…">
                        <label for="ticketSearch">
                            <i class="fa-kit fa-solid-magnifying-glass-lightbulb-bl me-3"></i> Zoek ticket
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-4" id="orderlinesList">
                @include('web.partials.orderlines-list', ['orderlines' => $orderlines, 'event' => $event])
            </div>

            <div class="text-center py-3 d-none" id="searchSpinner">
                <div class="spinner-border" role="status" aria-hidden="true"></div>
            </div>
            <div class="text-center py-3">
                <p class="text-center text-muted d-none" id="emptyState">Geen tickets gevonden.</p>
                <p class="text-center text-muted d-none" id="limitedState"></p>
            </div>
        </div>
    </section>
@endsection

@section('script_after')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput    = document.getElementById('ticketSearch');
            const section        = document.querySelector('section[data-search-url]');
            const searchUrl      = section.dataset.searchUrl;
            const listContainer  = document.getElementById('orderlinesList');
            const spinner        = document.getElementById('searchSpinner');
            const emptyState     = document.getElementById('emptyState');
            const limitedState   = document.getElementById('limitedState');

            const originalHtml   = listContainer.innerHTML;
            let debounceTimeout  = null;
            let currentAbortCtrl = null;

            function setLoading(isLoading) {
                spinner.classList.toggle('d-none', !isLoading);
            }

            function setEmptyState(show) {
                emptyState.classList.toggle('d-none', !show);
            }

            function setLimitedState(data) {
                if (data && data.has_more) {
                    limitedState.textContent = `Er worden maximaal ${data.limit} resultaten getoond. Verfijn je zoekterm voor meer gerichte resultaten.`;
                    limitedState.classList.remove('d-none');
                } else {
                    limitedState.textContent = '';
                    limitedState.classList.add('d-none');
                }
            }

            searchInput.addEventListener('input', function () {
                const q = this.value.trim();

                clearTimeout(debounceTimeout);

                if (currentAbortCtrl) {
                    currentAbortCtrl.abort();
                }

                if (q === '') {
                    listContainer.innerHTML = originalHtml;
                    setLoading(false);
                    setEmptyState(false);
                    setLimitedState(null);
                    return;
                }

                debounceTimeout = setTimeout(() => {
                    currentAbortCtrl = new AbortController();
                    setLoading(true);
                    setEmptyState(false);
                    setLimitedState(null);

                    fetch(`${searchUrl}?q=${encodeURIComponent(q)}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        signal: currentAbortCtrl.signal
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }

                            return response.json();
                        })
                        .then(data => {
                            setLoading(false);
                            listContainer.innerHTML = data.html || '';
                            setEmptyState(!data.count);
                            setLimitedState(data);
                        })
                        .catch(error => {
                            if (error.name === 'AbortError') {
                                return;
                            }

                            console.error('Search error:', error);
                            setLoading(false);
                            listContainer.innerHTML = '';
                            setEmptyState(true);
                            setLimitedState(null);
                        });
                }, 250);
            });
        });
    </script>
@endsection
