<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiService
{
    public function __construct(
        private readonly ?string $baseUri = null,
    ) {}

    public function getEventByUuid(string $uuid): array
    {
        return $this->get("get-event/{$uuid}");
    }

    public function getEventsByOrganisationId(int $organisationId): array
    {
        return $this->get("get-events/{$organisationId}");
    }

    public function getOrderlinesByEvent(int $eventId): array
    {
        return $this->get("get-orderlines/{$eventId}");
    }

    public function searchOrderlinesByEvent(int $eventId, string $q): ?array
    {
        if (trim($q) === '') {
            return $this->getOrderlinesByEvent($eventId);
        }

        return $this->get("search-orderlines/{$eventId}", ['q' => $q])['data'] ?? null;
    }

    public function checkinOrderline(string $orderlineUuid): array
    {
        return $this->post("manual/scan/checkin/{$orderlineUuid}");
    }

    public function checkoutOrderline(string $uuid): array
    {
        return $this->post("scan/checkout/{$uuid}");
    }

    /**
     * @param  list<int>  $ticketIds
     */
    public function scanTicket(string $eventUuid, string $qr, array $ticketIds): array
    {
        return $this->post("scan/checkin/{$eventUuid}", [
            'qr' => $qr,
            'tickets' => $ticketIds,
        ]);
    }

    private function get(string $path, array $query = []): array
    {
        try {
            $response = $this->client()->get($this->url($path), $query);

            return $response->successful()
                ? $response->json() ?? []
                : ['error' => 'Unable to fetch data from API', 'status' => $response->status()];
        } catch (\Throwable $exception) {
            Log::warning('Eventicks API GET failed', ['path' => $path, 'message' => $exception->getMessage()]);

            return ['error' => 'Unable to fetch data from API'];
        }
    }

    private function post(string $path, array $payload = []): array
    {
        try {
            $response = $this->client()->post($this->url($path), $payload);

            return $response->successful()
                ? $response->json() ?? []
                : ['error' => 'Unable to update API', 'status' => $response->status()];
        } catch (\Throwable $exception) {
            Log::warning('Eventicks API POST failed', ['path' => $path, 'message' => $exception->getMessage()]);

            return ['error' => 'Unable to update API'];
        }
    }

    private function client(): PendingRequest
    {
        $request = Http::acceptJson()
            ->timeout((int) config('services.api.timeout', 8))
            ->retry(2, 200, throw: false);

        $token = config('services.api.token');

        return $token ? $request->withToken($token) : $request;
    }

    private function url(string $path): string
    {
        $baseUri = rtrim($this->baseUri ?: (string) config('services.api.base_uri'), '/');

        return $baseUri.'/'.ltrim($path, '/');
    }
}
