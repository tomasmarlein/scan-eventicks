<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
    protected $baseUri;

    public function __construct()
    {
        $this->baseUri = config('services.api.base_uri');
    }
    // Pers
    public function getEventByUuid($uuid)
    {
        $response = Http::get("{$this->baseUri}/get-event/{$uuid}");

        if ($response->successful()) {
            return $response->json();
        }

        return ['error' => 'Unable to fetch data from API'];
    }

    public function getEventsByOrganisationId(int $organisationId)
    {
        $response = Http::get("{$this->baseUri}/get-events/{$organisationId}");

        if ($response->successful()) {
            return $response->json();
        }

        return ['error' => 'Unable to fetch data from API'];
    }

    public function getOrderlinesByEvent($eventId)
    {
        $response = Http::get("{$this->baseUri}/get-orderlines/{$eventId}");

        if ($response->successful()) {
            return $response->json();
        }

        return ['error' => 'Unable to fetch data from API'];
    }

    // In ApiService.php
    public function searchOrderlinesByEvent($eventId, string $q)
    {
        if (empty($q)) {
            // Geen zoekterm → geef alles terug
            return $this->getOrderlinesByEvent($eventId);
        }

        try {
            $response = Http::withToken($this->token)
                            ->get($this->baseUrl . "/search-orderlines/{$eventId}", [
                                'q' => $q,
                            ]);

            if ($response->successful()) {
                return $response->json('data'); // afhankelijk van API-structuur
            }
        } catch (\Throwable $e) {
            \Log::error("API search failed: " . $e->getMessage());
        }

        return null; // null → fallback in je controller
    }

    public function checkinOrderline($orderline_uuid)
    {
        $response = Http::post("{$this->baseUri}/manual/scan/checkin/{$orderline_uuid}");

        if ($response->successful()) {
            return $response->json();
        }

        return ['error' => 'Unable to fetch data from API'];
    }

    public function checkoutOrderline($uuid)
    {
        $response = Http::get("{$this->baseUri}/scan/checkout/{$uuid}");

        if ($response->successful()) {
            return $response->json();
        }

        return ['error' => 'Unable to fetch data from API'];
    }

    public function scanTicket($event_uuid, $qr, array $ticketIds)
    {
        $response = Http::post("{$this->baseUri}/scan/checkin/{$event_uuid}", [
            'qr' => $qr,
            'tickets' => $ticketIds,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return ['error' => 'Unable to fetch data from API'];
    }
}
