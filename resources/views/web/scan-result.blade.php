{{-- resources/views/scan/valid.blade.php --}}
@extends('template.template')

@section('main')
    @if($scan['status'] === 'success')
        <style>
            :root {
                --rl-primary: #EF7900;
            }
            html, body { height: 100%; overflow: hidden; background:#0b0b0b; }
            #valid-page {
                height: 100svh; min-height: 100svh;
                display: flex; flex-direction: column; align-items: stretch; justify-content: space-between;
                padding: 16px;
                background: radial-gradient(1200px 600px at 50% -300px, rgba(46, 204, 113,.25), transparent 65%);
            }
            .status-badge {
                display:inline-flex; align-items:center; gap:.5rem;
                background: rgba(46, 204, 113,.15); color:#2ecc71; border:1px solid rgba(46, 204, 113,.35);
                padding:.4rem .7rem; border-radius:999px; font-weight:600; font-size:.95rem;
                backdrop-filter: blur(6px);
            }
            .ticket-card {
                border-radius: 16px; background: #121212; border:1px solid #2a2a2a; color:#eee;
                box-shadow: 0 10px 30px rgba(0,0,0,.25);
            }
            .chip {
                display:inline-block; font-size:.75rem; border-radius:999px; padding:.25rem .6rem;
                background:#1d1d1d; border:1px solid #2d2d2d; color:#bbb;
            }
            .value { color:#fff; font-weight:600; }
            .divider { border-top:1px dashed #2d2d2d; margin:.75rem 0; }
            .cta-bar {
                position: sticky; bottom:0; left:0; right:0;
                display:flex; gap:.6rem; background:linear-gradient(180deg, rgba(11,11,11,0) 0%, rgba(11,11,11,1) 35%);
                padding-top:12px; padding-bottom:4px;
            }
            .btn-primary { background: var(--rl-primary); border-color: var(--rl-primary); }
            .btn-primary:active, .btn-primary:hover { filter: brightness(.95); }
            /* simpele “confetti” sparkle */
            .sparkle {
                position:absolute; inset:0; pointer-events:none;
                background:
                    radial-gradient(circle, rgba(46,204,113,.9) 0 2px, transparent 3px) -20px -10px/120px 120px,
                    radial-gradient(circle, rgba(239,121,0,.9)   0 2px, transparent 3px)  40px  10px/120px 120px,
                    radial-gradient(circle, rgba(255,255,255,.8) 0 2px, transparent 3px)  10px  30px/120px 120px;
                animation: float 3.2s linear infinite;
                opacity:.55;
            }
            @keyframes float {
                from { background-position: -20px -10px, 40px 10px, 10px 30px; }
                to   { background-position: -20px calc(-10px + 120px), 40px calc(10px + 120px), 10px calc(30px + 120px); }
            }
        </style>

        <div id="valid-page">
            <div class="position-relative">
                <div class="sparkle"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative" style="z-index:2">
                <span class="status-badge">
                <i class="fa-solid fa-circle-check"></i> Geldig ticket
            </span>
                </div>

                <div class="ticket-card p-3 position-relative" style="z-index:2">
                    {{-- Kop --}}
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="chip mb-1">{{ $event['name'] ?? 'Mol-Centrum Rozenberg Lichtstoet' }}</div>
                            <h1 class="h4 m-0">
                                Check-in geslaagd
                            </h1>
                            <div class="text-secondary small">
                                {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} • Scanner #{{ auth()->id() }}
                            </div>
                        </div>
                        <div class="text-end">
                            <i class="fa-solid fa-ticket-simple" style="font-size:28px; color:#888;"></i>
                        </div>
                    </div>

                    <div class="divider"></div>

                    {{-- Kerngegevens van het ticket --}}
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="text-secondary small">Naam</div>
                            <div class="value">{{ $scan['orderline']['name'] ?? 'Onbekend' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small">Ticket</div>
                            <div class="value">{{ $scan['orderline']['ticket']['name'] ?? 'Onbekend' }}</div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="text-secondary small">Categorie</div>
                            <div class="value">{{ $scan['orderline']['ticket']['name'] ?? 'Onbekend' }}</div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    {{-- Extra info / metadata --}}
                    <div class="d-flex flex-wrap gap-2">
                        <span class="chip">ID: {{ $scan['orderline']['unique_qr_id'] ?? '—' }}</span>
                        <span class="chip">Order: {{ $scan['order_ref'] ?? '—' }}</span>
                        <span class="chip">Zone: {{ $scan['zone'] ?? 'Algemeen' }}</span>
                        <span class="chip">Prijs: €{{ number_format($scan['orderline']['ticket']['price'] ?? 0, 2, ',', '.') }}</span>
                    </div>

                    @if(!empty($scan['message']))
                        <div class="alert alert-success mt-3 mb-0 py-2 px-3">
                            <i class="fa-solid fa-check me-2"></i>{{ $scan['message'] }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actieknoppen onderaan --}}
            <div class="cta-bar">
                <form id="resultForm" class="w-100" method="POST" action="{{ route('scan.camera', $event['uuid']) }}">
                    @method('POST')
                    @csrf
                    <input type="hidden" name="tickets" value='@json($tickets)'>

                    <button type="submit" class="btn btn-primary w-100" id="btn-next-hidden">
                        <i class="fa-solid fa-qrcode me-2"></i>Volgende scan
                    </button>
                </form>
            </div>
        </div>

{{--        <script>--}}
{{--            // Trillen bij success (als beschikbaar)--}}
{{--            if (navigator.vibrate) { navigator.vibrate([40, 30, 40]); }--}}

{{--            // // Automatisch na 5 seconden het form submitten--}}
{{--            setTimeout(() => {--}}
{{--                document.getElementById('resultForm')?.submit();--}}
{{--            }, 5000);--}}
{{--        </script>--}}
    @else
        <style>
            :root {
                --rl-primary: #EF7900;
            }
            html, body { height: 100%; overflow: hidden; background:#0b0b0b; }
            #invalid-page {
                height: 100svh; min-height: 100svh;
                display: flex; flex-direction: column; align-items: stretch; justify-content: space-between;
                padding: 16px;
                background: radial-gradient(1200px 600px at 50% -300px, rgba(231,76,60,.25), transparent 65%);
            }
            .status-badge {
                display:inline-flex; align-items:center; gap:.5rem;
                background: rgba(231, 76, 60,.15); color:#e74c3c; border:1px solid rgba(231,76,60,.35);
                padding:.4rem .7rem; border-radius:999px; font-weight:600; font-size:.95rem;
                backdrop-filter: blur(6px);
            }
            .ticket-card {
                border-radius: 16px; background: #121212; border:1px solid #2a2a2a; color:#eee;
                box-shadow: 0 10px 30px rgba(0,0,0,.25);
            }
            .chip {
                display:inline-block; font-size:.75rem; border-radius:999px; padding:.25rem .6rem;
                background:#1d1d1d; border:1px solid #2d2d2d; color:#bbb;
            }
            .value { color:#fff; font-weight:600; }
            .divider { border-top:1px dashed #2d2d2d; margin:.75rem 0; }
            .cta-bar {
                position: sticky; bottom:0; left:0; right:0;
                display:flex; gap:.6rem; background:linear-gradient(180deg, rgba(11,11,11,0) 0%, rgba(11,11,11,1) 35%);
                padding-top:12px; padding-bottom:4px;
            }
            .btn-danger { background: #e74c3c; border-color: #e74c3c; }
            .btn-danger:active, .btn-danger:hover { filter: brightness(.95); }
        </style>

        <div id="invalid-page">
            <div class="position-relative">
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative" style="z-index:2">
        <span class="status-badge">
            <i class="fa-solid fa-circle-xmark"></i> Ongeldig ticket
        </span>
                </div>

                <div class="ticket-card p-3 position-relative" style="z-index:2">
                    {{-- Kop --}}
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="chip mb-1">{{ $event['name'] ?? 'Mol-Centrum Rozenberg Lichtstoet' }}</div>
                            <h1 class="h4 m-0">Check-in mislukt</h1>
                            <div class="text-secondary small">
                                {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} • Scanner #{{ auth()->id() }}
                            </div>
                        </div>
                        <div class="text-end">
                            <i class="fa-solid fa-ticket-simple" style="font-size:28px; color:#888;"></i>
                        </div>
                    </div>

                    <div class="divider"></div>

                    {{-- Kerngegevens van het ticket --}}
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="text-secondary small">Naam</div>
                            <div class="value">{{ $scan['orderline']['name'] ?? 'Onbekend' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small">Ticket</div>
                            <div class="value">{{ $scan['orderline']['ticket']['name'] ?? 'Onbekend' }}</div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="text-secondary small">Categorie</div>
                            <div class="value">{{ $scan['orderline']['ticket']['name'] ?? 'Onbekend' }}</div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    {{-- Extra info / metadata --}}
                    <div class="d-flex flex-wrap gap-2">
                        <span class="chip">ID: {{ $scan['orderline']['unique_qr_id'] ?? '—' }}</span>
                        <span class="chip">Order: {{ $scan['order_ref'] ?? '—' }}</span>
                        <span class="chip">Zone: {{ $scan['zone'] ?? 'Algemeen' }}</span>
                        <span class="chip">Prijs: €{{ number_format($scan['orderline']['ticket']['price'] ?? 0, 2, ',', '.') }}</span>
                    </div>

                    @if(!empty($scan['message']))
                        <div class="alert alert-danger mt-3 mb-0 py-2 px-3">
                            <i class="fa-solid fa-xmark me-2"></i>{{ $scan['message'] }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actieknoppen onderaan --}}
            <div class="cta-bar">
                <form id="resultForm" class="w-100" method="POST" action="{{ route('scan.camera', $event['uuid']) }}">
                    @method('POST')
                    @csrf
                    <input type="hidden" name="tickets" value='@json($tickets)'>

                    <button type="submit" class="btn btn-danger w-100" id="btn-next-hidden">
                        <i class="fa-solid fa-qrcode me-2"></i>Nieuwe scan
                    </button>
                </form>
            </div>
        </div>

{{--        <script>--}}
{{--            // Trillen bij fout (heftiger)--}}
{{--            if (navigator.vibrate) { navigator.vibrate([80, 50, 80]); }--}}

{{--            // Automatisch na 5 seconden terug naar camera--}}
{{--            setTimeout(() => {--}}
{{--                document.getElementById('resultForm')?.submit();--}}
{{--            }, 5000);--}}
{{--        </script>--}}
    @endif
@endsection

@section('script_after')
    <script>
        (function() {
            const status = "{{ $scan['status'] }}"; // success of error
            const already = sessionStorage.getItem('hapticDone') === '1';
            const pattern = status === 'success' ? [40, 30, 40] : [80, 50, 80];
            const soundFile = status === 'success' ? '/sounds/success.mp3' : '/sounds/error.mp3';

            // 🔊 Functie om geluid af te spelen
            function playSound() {
                try {
                    const audio = new Audio(soundFile);
                    audio.volume = 0.8;
                    audio.play().catch(() => {
                        // Autoplay kan geblokkeerd zijn, dan vangt pointerdown-fallback dit op
                    });
                } catch (e) { console.error(e); }
            }

            // 🔈 Probeer direct te trillen & geluid afspelen
            if (!already && document.visibilityState === 'visible') {
                if ('vibrate' in navigator) navigator.vibrate(pattern);
                playSound();
            }

            // ⏱ Automatisch na 5 s opnieuw naar de camera
            setTimeout(() => {
                document.getElementById('resultForm')?.submit();
            }, 5000);

            // 🧹 Reset vlag zodat volgende scan opnieuw geluid geeft
            try { sessionStorage.removeItem('hapticDone'); } catch (e) {}

            // 🩷 Extra fallback: als browser user gesture eist
            window.addEventListener('pointerdown', function once() {
                playSound();
                if ('vibrate' in navigator) navigator.vibrate(pattern);
                window.removeEventListener('pointerdown', once);
            }, { once: true });
        })();
    </script>
@endsection
