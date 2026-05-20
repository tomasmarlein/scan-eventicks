<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesScannerContext;
use App\Models\Orderline;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ScanController extends Controller
{
    use ResolvesScannerContext;

    public function index(string $org_slug, string $slug)
    {
        $organisation = $this->resolveOrganisation($org_slug);

        $event = $this->resolveEventForOrganisation(
            $organisation,
            $slug,
            ['tickets'],
            ['id', 'uuid', 'organisation_id', 'name', 'slug'],
        );

        return view('web.tickets', [
            'organisation' => $organisation,
            'event' => $event,
        ]);
    }

    public function camera(Request $request, string $org_slug, string $slug)
    {
        $organisation = $this->resolveOrganisation($org_slug);

        $event = $this->resolveEventForOrganisation(
            $organisation,
            $slug,
            ['tickets'],
            ['id', 'uuid', 'organisation_id', 'name', 'slug'],
        );

        $tickets = $this->extractTicketIds($request->input('tickets', []));
        $validTicketIds = $event->tickets->pluck('id')->map(fn ($id) => (int) $id)->all();
        $tickets = array_values(array_intersect($tickets, $validTicketIds));

        if ($tickets === []) {
            return back()->withErrors([
                'tickets' => 'Er zijn geen geldige tickettypes geselecteerd. Selecteer minstens 1 tickettype om te scannen.',
            ])->withInput();
        }

        return view('web.scan', [
            'organisation' => $organisation,
            'event' => $event,
            'event_id' => $event->id,
            'tickets' => $tickets,
            'feedbackMs' => (int) config('scanner.scan_feedback_ms', 1400),
        ]);
    }

    public function store(string $org_slug, string $event_slug, Request $request)
    {
        $organisation = $this->resolveOrganisation($org_slug);

        $event = $this->resolveEventForOrganisation(
            $organisation,
            $event_slug,
            [],
            ['id', 'uuid', 'organisation_id', 'name', 'slug'],
        );

        $data = $request->validate([
            'qr' => ['required', 'string', 'max:4096'],
            'tickets' => ['nullable'],
        ]);

        $qr = $this->normalizeQrValue($data['qr']);
        $tickets = $this->extractTicketIds($request->input('tickets', []));
        $scan = $this->baseScanPayload($qr);

        try {
            $scan = DB::connection((new Orderline)->getConnectionName())->transaction(function () use ($event, $qr, $tickets, $scan) {
                $query = Orderline::query()
                    ->matchingQr($qr)
                    ->with('ticket')
                    ->lockForUpdate();

                $columns = Orderline::scannerSelectColumns();

                if ($columns !== []) {
                    $query->select($columns);
                }

                $orderline = $query->first();

                if (! $orderline instanceof Orderline) {
                    return array_replace_recursive($scan, [
                        'status' => 'error',
                        'message' => 'Ticket bestaat niet.',
                    ]);
                }

                $payload = $this->scanPayloadFromOrderline($scan, $orderline, $qr);

                if ((int) $orderline->event_id !== (int) $event->id) {
                    return array_replace_recursive($payload, [
                        'status' => 'error',
                        'message' => 'Ticket hoort bij een ander evenement.',
                    ]);
                }

                if ($tickets !== [] && ! in_array((int) $orderline->ticket_id, $tickets, true)) {
                    return array_replace_recursive($payload, [
                        'status' => 'error',
                        'message' => 'Dit tickettype is niet geselecteerd voor deze scanner.',
                    ]);
                }

                if ((bool) ($orderline->blocked ?? false)) {
                    return array_replace_recursive($payload, [
                        'status' => 'warning',
                        'message' => 'Ticket is geblokkeerd.',
                    ]);
                }

                if ((bool) ($orderline->scanned ?? false)) {
                    return array_replace_recursive($payload, [
                        'status' => 'warning',
                        'message' => 'Ticket is al ingecheckt.',
                    ]);
                }

                $orderline->forceFill(['scanned' => true])->save();
                $this->incrementScannerCount();

                return array_replace_recursive($payload, [
                    'status' => 'success',
                    'message' => 'Geldig ticket. Check-in geslaagd.',
                ]);
            }, 3);
        } catch (Throwable $exception) {
            report($exception);

            $scan = array_replace_recursive($scan, [
                'status' => 'error',
                'message' => 'Scan kon niet verwerkt worden. Probeer opnieuw.',
            ]);
        }

        return $this->renderScanResult($event, $organisation, $scan, $tickets, $request);
    }

    private function renderScanResult($event, $organisation, array $scan, array $tickets, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => $scan['status'],
                'message' => $scan['message'],
                'feedback_ms' => (int) config('scanner.scan_feedback_ms', 1400),
                'scan' => $scan,
            ]);
        }

        return view('web.scan-result', [
            'organisation' => $organisation,
            'event_uuid' => $event->uuid ?? null,
            'event' => $event,
            'scan' => $scan,
            'tickets' => $tickets,
        ]);
    }

    /**
     * @return list<int>
     */
    private function extractTicketIds(mixed $input): array
    {
        if (is_string($input)) {
            $decoded = json_decode($input, true);
            $input = json_last_error() === JSON_ERROR_NONE ? $decoded : [$input];
        }

        if (! is_array($input)) {
            return [];
        }

        return collect($input)
            ->flatten()
            ->map(fn ($value) => filter_var($value, FILTER_VALIDATE_INT))
            ->filter(fn ($value) => $value !== false && (int) $value > 0)
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeQrValue(string $qr): string
    {
        $qr = trim($qr);

        if ($qr === '' || ! str_contains($qr, '://')) {
            return $qr;
        }

        $parts = parse_url($qr);

        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);

            foreach (['qr', 'uuid', 'ticket', 'code'] as $key) {
                if (! empty($query[$key]) && is_string($query[$key])) {
                    return trim($query[$key]);
                }
            }
        }

        $path = trim((string) ($parts['path'] ?? ''), '/');

        if ($path === '') {
            return $qr;
        }

        $segments = explode('/', $path);

        return trim((string) end($segments));
    }

    private function baseScanPayload(string $qr): array
    {
        return [
            'status' => 'error',
            'message' => 'Onbekende fout.',
            'zone' => 'Algemeen',
            'order_ref' => null,
            'orderline' => [
                'name' => null,
                'unique_qr_id' => $qr,
                'ticket' => [
                    'name' => null,
                    'type' => null,
                    'price' => null,
                ],
            ],
        ];
    }


    private function incrementScannerCount(): void
    {
        try {
            auth()->user()?->increment('scan_count');
        } catch (Throwable) {
            // Older ticket databases may not have scan_count yet. A missing
            // counter must never make a valid ticket scan fail.
        }
    }

    private function scanPayloadFromOrderline(array $scan, Orderline $orderline, string $qr): array
    {
        return array_replace_recursive($scan, [
            'order_ref' => $orderline->order_reference ?? null,
            'zone' => $orderline->zone ?? 'Algemeen',
            'orderline' => [
                'name' => $orderline->name ?? null,
                'unique_qr_id' => $orderline->displayQr() ?? $qr,
                'ticket' => [
                    'name' => $orderline->ticket?->name,
                    'type' => $orderline->ticket->type,
                    'price' => $orderline->ticket?->price,
                ],
            ],
        ]);
    }
}
