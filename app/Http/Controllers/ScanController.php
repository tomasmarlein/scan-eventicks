<?php

namespace App\Http\Controllers;

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

    public function index($uuid)
    {
        $event = $this->apiService->getEventByUuid($uuid);

        return view('web.tickets', [
            'event' => $event,
        ]);
    }

    public function camera(Request $request, $uuid)
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

        $event = $this->apiService->getEventByUuid($uuid);

        return view('web.scan', [
            'event'    => $event,
            'event_id' => $event['id'],
            'tickets'  => $tickets,
        ]);
    }

    public function store($event_uuid, Request $request)
    {
        $event = $this->apiService->getEventByUuid($event_uuid);

        $data = $request->validate([
                                       'qr' => ['required', 'string', 'max:4096'],
                                   ]);

        $qr = trim($data['qr']);

        $tickets = json_decode($request->input('tickets', '[]'), true);

        $scan = $this->apiService->scanTicket($event_uuid, $qr, $tickets);

        if (!$scan) {
            if ($request->expectsJson()) {
                return response()->json([
                                            'status' => 'error',
                                                                                                                                                                                                                                                  'message' => 'Er is iets misgegaan bij het scannen van de ticket. Probeer het opnieuw.',
                                        ], 422);
            }

            smilify('error', 'Er is iets misgegaan bij het scannen van de ticket. Probeer het opnieuw.');

            return redirect()->route('scan.camera', ['uuid' => $event_uuid]);
        }

        // Get user + scan count
        $user = User::find(auth()->id());
        $user?->increment('scan_count');

        if ($request->expectsJson()) {
            return response()->json([
                                        'status'     => $scan['status'],   // bv. 'success' | 'warning' | 'error'
                                        'message'    => $scan['message'],
                                        'scan_count' => $user->scan_count,
                                    ]);
        }

//        smilify($scan['status'], $scan['message']);
        return view('web.scan-result', [
            'event_uuid' => $event_uuid,
            'event'      => $event,
            'scan'       => $scan,
            'tickets'    => $tickets,
        ]);
    }
}
