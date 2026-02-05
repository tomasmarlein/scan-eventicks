<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManuelController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index($uuid)
    {
        $event = $this->apiService->getEventByUuid($uuid);
        abort_if(!$event, 404);

        $orderlines = $this->apiService->getOrderlinesByEvent($event['id']) ?? [];

        // voeg actie-URLs toe
        $orderlines = collect($orderlines)->map(function ($ol) use ($uuid) {
            $orderline_uuid = $ol['orderline_uuid'] ?? $ol['uuid']; // welke key je API ook gebruikt

            return array_merge($ol, [
                'url_checkin'  => route('manuel.checkin', ['uuid' => $uuid, 'orderline_uuid' => $orderline_uuid]),
                'url_checkout' => route('manuel.checkout', ['uuid' => $uuid, 'orderline_uuid' => $orderline_uuid]),
            ]);
        })->all();

        return view('web.manuel', [
            'event'      => $event,
            'orderlines' => $orderlines,
        ]);
    }

    public function search($uuid, Request $request)
    {
        $event = $this->apiService->getEventByUuid($uuid);
        abort_if(!$event, 404);

        $q = trim((string) $request->input('q', ''));

        // Probeer eerst via de API search
        $results = $this->apiService->searchOrderlinesByEvent($event['id'], $q);

        // Fallback: lokaal filteren als de API geen search ondersteunt of null teruggeeft
        if ($results === null) {
            $all    = $this->apiService->getOrderlinesByEvent($event['id']) ?? [];
            $needle = Str::lower($q);

            $results = collect($all)->filter(function ($ol) use ($needle) {
                if ($needle === '') {
                    return true;
                }

                // Exact match voor QR / UUID (handig bij plakken van QR-code)
                $qr = Str::lower((string) (
                    $ol['unique_qr_code'] ?? // als je die key hebt
                    $ol['unique_qr_id'] ??
                    $ol['uuid'] ?? ''
                ));

                if ($qr !== '' && $qr === $needle) {
                    return true;
                }

                // Algemene fuzzy search (naam, qr, mail, referentie…)
                $hay = Str::lower(
                    ($ol['name'] ?? '') . ' ' .
                    ($ol['unique_qr_code'] ?? '') . ' ' .
                    ($ol['unique_qr_id'] ?? '') . ' ' .
                    ($ol['email'] ?? '') . ' ' .
                    ($ol['order_reference'] ?? '')
                );

                return Str::contains($hay, $needle);
            })->values();
        } else {
            $results = collect($results);
        }

        // Routes + eventuele aliases toevoegen
        $orderlines = $results->map(function ($ol) use ($uuid) {
            $id = $ol['orderline_uuid'] ?? $ol['uuid'] ?? null;

            // alias zodat je in Blade consequent kunt zijn
            $ol['unique_qr_code'] = $ol['unique_qr_code']
                                    ?? $ol['unique_qr_id']
                                       ?? $ol['uuid']
                                          ?? null;

            $ol['url_checkin']  = $id ? route('manuel.checkin', ['uuid' => $uuid, 'orderline_uuid' => $id]) : null;
            $ol['url_checkout'] = $id ? route('manuel.checkout', ['uuid' => $uuid, 'orderline_uuid' => $id]) : null;

            return $ol;
        });

        // Render dezelfde partial als in de eerste pageload
        $html = view('web.partials.orderlines-list', [
            'orderlines' => $orderlines,
            'event'      => $event,
        ])->render();

        return response()->json([
                                    'html'  => $html,
                                    'count' => $orderlines->count(),
                                ]);
    }

    public function checkin($uuid, $orderline_uuid)
    {
        $event = $this->apiService->getEventByUuid($uuid);
        abort_if(!$event, 404);

        $response = $this->apiService->checkinOrderline($orderline_uuid);

        if (isset($response['error'])) {
            smilify('error', 'Inchecken is niet gelukt.');

            return redirect()->route('scan.manuel', ['uuid' => $uuid]);
        }

        smilify('success', $response['message'] ?? 'Ticket is succesvol ingecheckt.');
        return redirect()->route('scan.manuel', ['uuid' => $uuid]);
    }

    public function checkout($uuid, $orderline_uuid)
    {
        $event = $this->apiService->getEventByUuid($uuid);
        abort_if(!$event, 404);

        // Call API to checkin
        $response = $this->apiService->checkoutOrderline($orderline_uuid);

        if (isset($response['error'])) {
            smilify('error', 'Uitchecken is niet gelukt.');

            return redirect()->route('scan.manuel', ['uuid' => $uuid]);
        }

        smilify('success', 'Ticket is succesvol Uitgecheckt.');

        return redirect()->route('scan.manuel', ['uuid' => $uuid]);
    }
}
