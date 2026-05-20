<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesScannerContext;
use App\Models\Orderline;
use App\Models\Ticket;

class OrganisationOverviewController extends Controller
{
    use ResolvesScannerContext;

    public function index(string $slug)
    {
        $organisation = $this->resolveOrganisation($slug);

        $events = $organisation->events()
            ->select(['id', 'organisation_id', 'name', 'slug', 'address', 'postcode', 'plaats', 'start'])
            ->orderByDesc('start')
            ->get();

        $eventIds = $events->pluck('id')->map(fn ($id) => (int) $id)->values();

        $soldByEvent = $eventIds->isEmpty()
            ? collect()
            : Ticket::query()
                ->whereIn('event_id', $eventIds)
                ->selectRaw('event_id, COALESCE(SUM(verkochte_tickets), 0) as aggregate')
                ->groupBy('event_id')
                ->pluck('aggregate', 'event_id');

        $scannedByEvent = $eventIds->isEmpty()
            ? collect()
            : Orderline::query()
                ->whereIn('event_id', $eventIds)
                ->where('scanned', true)
                ->selectRaw('event_id, COUNT(*) as aggregate')
                ->groupBy('event_id')
                ->pluck('aggregate', 'event_id');

        $events->each(function ($event) use ($soldByEvent, $scannedByEvent) {
            $sold = (int) ($soldByEvent[$event->id] ?? 0);
            $scanned = (int) ($scannedByEvent[$event->id] ?? 0);

            $event->setAttribute('sold_tickets', $sold);
            $event->setAttribute('scanned_tickets', $scanned);
            $event->setAttribute('scan_percentage', $sold > 0 ? round(($scanned / $sold) * 100, 1) : 0);
        });

        return view('web.overview', [
            'organisation' => $organisation,
            'events' => $events,
        ]);
    }
}
