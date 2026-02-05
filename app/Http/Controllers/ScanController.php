<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Orderline;
use App\Models\Organisation;
use App\Models\User;
use App\Services\ApiService;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index($org_slug, $slug)
    {
        $organisation = Organisation::where('slug', $org_slug)->first();

        if (!$organisation) {
            abort(404);
        }

        $event = Event::with('tickets.orderlines')->where('slug', $slug)->first();

        if (!$event) {
            abort(404);
        }

        return view('web.tickets', [
            'organisation' => $organisation,
            'event'        => $event,
        ]);
    }

    public function camera(Request $request, $org_slug, $slug)
    {
        $request->validate([
                               'tickets' => 'required',
                           ],
                           [
                               'tickets.required' => 'Er zijn geen tickets geselecteerd. Je moet minstens 1 ticket selecteren om te scannen.',
                           ]);

        $tickets = $request->tickets;

        // Check if tickets is a JSON string and decode it
        if (is_string($tickets)) {
            $tickets = json_decode($tickets, true);
        }

        $organisation = Organisation::where('slug', $org_slug)->first();

        if (!$organisation) {
            abort(404);
        }

        $event = Event::with('tickets.orderlines')->where('slug', $slug)->first();

        if (!$event) {
            abort(404);
        }

        return view('web.scan', [
            'organisation' => $organisation,
            'event'        => $event,
            'event_id'     => $event['id'],
            'tickets'      => $tickets,
        ]);
    }

    public function store($org_slug, $event_slug, Request $request)
    {
        $organisation = Organisation::where('slug', $org_slug)->firstOrFail();

        $event = Event::with('tickets.orderlines')->where('slug', $event_slug)->firstOrFail();

        $data = $request->validate([
           'qr'      => ['required', 'string', 'max:4096'],
           'tickets' => ['nullable'],
        ]);

        $qr = trim($data['qr']);

        // Basis scan response (altijd aanwezig)
        $scan = [
            'status'   => 'error',   // success|warning|error
            'message'  => 'Onbekende fout.',
            'zone'     => 'Algemeen',
            'order_ref'=> null,
            'orderline'=> [
                'name' => null,
                'unique_qr_id' => $qr,
                'ticket' => [
                    'name'  => null,
                    'price' => null,
                ],
            ],
        ];

        $tickets = json_decode($request->input('tickets', '[]'), true) ?: [];

        // Get orderline
        $orderline = Orderline::where('uuid', $qr)->first();

        if (!$orderline) {
            $scan['status']  = 'error';
            $scan['message'] = 'Ticket bestaat niet.';
            return $this->renderScanResult($event, $organisation, $scan, $tickets, $request);
        }

        $scan['order_ref'] = $orderline->order_reference ?? null;
        $scan['zone']      = $orderline->zone ?? 'Algemeen';
        $scan['orderline'] = [
            'name' => $orderline->name ?? null,
            'unique_qr_id' => $orderline->unique_qr_id ?? $orderline->uuid ?? $qr,
            'ticket' => [
                'name'  => optional($orderline->ticket)->name,
                'price' => optional($orderline->ticket)->price,
            ],
        ];

        /**
         * 2) Verkeerd event
         */
        if ((int) $orderline->event_id !== (int) $event->id) {
            $scan['status']  = 'error';
            $scan['message'] = 'Ticket hoort bij een ander evenement.';
            return $this->renderScanResult($event, $organisation, $scan, $tickets, $request);
        }

        /**
         * 3) Geblokkeerd / al gescand
         * Ik neem aan: blocked=true betekent "al ingecheckt".
         * Als jij een ander veld hebt (checked_in_at), zeg het en ik pas aan.
         */
        if ((bool) ($orderline->blocked ?? false)) {
            $scan['status']  = 'warning';
            $scan['message'] = 'Ticket is al ingecheckt (al gescand).';
            return $this->renderScanResult($event, $organisation, $scan, $tickets, $request);
        }

        /**
         * 4) Geldig: markeer als gescand + success
         */
        $orderline->blocked = true;
        // $orderline->checked_in_at = now(); // als je dit veld hebt
        // $orderline->checked_in_by = auth()->id(); // idem
        $orderline->save();

        $scan['status']  = 'success';
        $scan['message'] = 'Geldig ticket. Check-in geslaagd.';

        return $this->renderScanResult($event, $organisation, $scan, $tickets, $request);
    }

    private function renderScanResult($event, $organisation, array $scan, array $tickets, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status'     => $scan['status'],
                'message'    => $scan['message'],
                'scan'       => $scan,
            ]);
        }

        return view('web.scan-result', [
            'organisation' => $organisation,
            'event_uuid' => $event->uuid ?? null,
            'event'      => $event,
            'scan'       => $scan,
            'tickets'    => $tickets,
        ]);
    }
}
