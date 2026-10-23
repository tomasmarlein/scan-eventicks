<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesScannerContext;
use App\Models\Orderline;
use App\Models\Organisation;
use App\Models\Ticket;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    use ResolvesScannerContext;

    public function index()
    {
        $organisations = Organisation::query()
            ->select(['id', 'name', 'slug'])
            ->forUser(auth()->user())
            ->get();

        return view('web.organisations', [
            'organisations' => $organisations,
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = trim($validated['q'] ?? '');
        $organisations = Organisation::query()
            ->select(['id', 'name', 'slug'])
            ->forUser($request->user())
            ->when($query !== '', fn ($builder) => $builder->where('name', 'like', '%'.addcslashes($query, '%_\\').'%'))
            ->orderBy('name')
            ->get();

        return response()->json([
            'html' => view('web.partials.organisation-list', compact('organisations'))->render(),
            'count' => $organisations->count(),
        ]);
    }

    public function event(string $org_slug, string $slug)
    {
        $organisation = $this->resolveOrganisation($org_slug);

        $event = $this->resolveEventForOrganisation(
            $organisation,
            $slug,
            ['tickets'],
            ['id', 'uuid', 'organisation_id', 'name', 'slug', 'address', 'postcode', 'plaats', 'start'],
        );

        $ticketIds = $event->tickets->pluck('id')->map(fn ($id) => (int) $id)->values();

        $countsByTicket = $ticketIds->isEmpty()
            ? collect()
            : Orderline::query()
                ->forEvent((int) $event->id)
                ->whereIn('ticket_id', $ticketIds)
                ->selectRaw('ticket_id, COUNT(*) as total, SUM(CASE WHEN scanned = 1 THEN 1 ELSE 0 END) as scanned_total')
                ->groupBy('ticket_id')
                ->get()
                ->keyBy('ticket_id');

        $event->tickets->each(function (Ticket $ticket) use ($countsByTicket) {
            $counts = $countsByTicket->get($ticket->id);
            $ticket->setAttribute('sold_tickets', (int) ($counts?->total ?? 0));
            $ticket->setAttribute('scanned_tickets', (int) ($counts?->scanned_total ?? 0));
        });

        $soldTickets = (int) $event->tickets->sum('sold_tickets');
        $scannedTickets = (int) $event->tickets->sum('scanned_tickets');

        $event->setAttribute('sold_tickets', $soldTickets);
        $event->setAttribute('scanned_tickets', $scannedTickets);
        $event->setAttribute('to_scan_tickets', max(0, $soldTickets - $scannedTickets));

        return view('web.event', [
            'organisation' => $organisation,
            'event' => $event,
        ]);
    }
}
