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
                    <h5 class="text-white">{{ $event['name'] }}</h5>
                    <div class="ck-text">
                        @php
                            $sold_tickets = 0;

                            foreach ($event['tickets'] as $ticket) {
                                $sold_tickets += $ticket['verkochte_tickets'];
                            }
                        @endphp

                        {{ $sold_tickets }} tickets
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="section"
             data-search-url="{{ route('events.tickets.search', [$organisation['slug'], $event['slug']]) }}">
        <div class="container">
            <div class="row g-4 mb-3">
                <div class="col-12">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control p-4 bg-gray border-1" id="ticketSearch"
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

            const originalHtml   = listContainer.innerHTML; // om terug te zetten bij lege zoekterm
            let debounceTimeout  = null;
            let currentAbortCtrl = null;

            function setLoading(isLoading) {
                if (isLoading) {
                    spinner.classList.remove('d-none');
                } else {
                    spinner.classList.add('d-none');
                }
            }

            function setEmptyState(show) {
                if (show) {
                    emptyState.classList.remove('d-none');
                } else {
                    emptyState.classList.add('d-none');
                }
            }

            searchInput.addEventListener('input', function () {
                const q = this.value.trim();

                // debounce
                clearTimeout(debounceTimeout);

                // abort vorige request
                if (currentAbortCtrl) {
                    currentAbortCtrl.abort();
                }

                // als veld leeg is: toon originele lijst en geen empty state
                if (q === '') {
                    listContainer.innerHTML = originalHtml;
                    setLoading(false);
                    setEmptyState(false);
                    return;
                }

                debounceTimeout = setTimeout(() => {
                    currentAbortCtrl = new AbortController();
                    setLoading(true);
                    setEmptyState(false);

                    const url = `${searchUrl}?q=${encodeURIComponent(q)}`;

                    fetch(url, {
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

                            if (!data.count || data.count === 0) {
                                setEmptyState(true);
                            } else {
                                setEmptyState(false);
                            }
                        })
                        .catch(error => {
                            if (error.name === 'AbortError') {
                                // nieuwe request heeft de oude geannuleerd, geen probleem
                                return;
                            }
                            console.error('Search error:', error);
                            setLoading(false);
                            listContainer.innerHTML = '';
                            setEmptyState(true);
                        });
                }, 300); // 300ms debounce
            });
        });
    </script>
@endsection
