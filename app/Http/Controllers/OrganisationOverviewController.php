<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesScannerContext;
use App\Models\Orderline;

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

        $countsByEvent = $eventIds->isEmpty()
            ? collect()
            : Orderline::query()
                ->whereIn('event_id', $eventIds)
                ->selectRaw('event_id, COUNT(*) as total, SUM(CASE WHEN scanned = 1 THEN 1 ELSE 0 END) as scanned_total')
                ->groupBy('event_id')
                ->get()
                ->keyBy('event_id');

        $events->each(function ($event) use ($countsByEvent) {
            $counts = $countsByEvent->get($event->id);
            $sold = (int) ($counts?->total ?? 0);
            $scanned = (int) ($counts?->scanned_total ?? 0);

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
