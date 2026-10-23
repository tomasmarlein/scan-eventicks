<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesScannerContext;
use App\Models\Organisation;
use App\Models\Orderline;
use Illuminate\Http\Request;

class OrganisationOverviewController extends Controller
{
    use ResolvesScannerContext;

    public function index(string $slug)
    {
        $organisation = $this->resolveOrganisation($slug);
        $events = $this->eventsWithCounts($organisation);

        return view('web.overview', [
            'organisation' => $organisation,
            'events' => $events,
        ]);
    }

    public function search(string $slug, Request $request)
    {
        $organisation = $this->resolveOrganisation($slug);
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $events = $this->eventsWithCounts($organisation, trim($validated['q'] ?? ''));

        return response()->json([
            'html' => view('web.partials.event-list', compact('organisation', 'events'))->render(),
            'count' => $events->count(),
        ]);
    }

    private function eventsWithCounts(Organisation $organisation, string $query = '')
    {
        $events = $organisation->events()
            ->select(['id', 'organisation_id', 'name', 'slug', 'address', 'postcode', 'plaats', 'start'])
            ->when($query !== '', fn ($builder) => $builder->where('name', 'like', '%'.addcslashes($query, '%_\\').'%'))
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

        return $events;
    }
}
