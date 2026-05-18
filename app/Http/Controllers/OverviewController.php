<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesScannerContext;
use App\Models\Orderline;
use App\Models\Organisation;
use App\Models\Ticket;

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

        $scannedByTicket = $ticketIds->isEmpty()
            ? collect()
            : Orderline::query()
                ->forEvent((int) $event->id)
                ->whereIn('ticket_id', $ticketIds)
                ->where('scanned', true)
                ->selectRaw('ticket_id, COUNT(*) as aggregate')
                ->groupBy('ticket_id')
                ->pluck('aggregate', 'ticket_id');

        $event->tickets->each(function (Ticket $ticket) use ($scannedByTicket) {
            $ticket->setAttribute('sold_tickets', (int) ($ticket->verkochte_tickets ?? 0));
            $ticket->setAttribute('scanned_tickets', (int) ($scannedByTicket[$ticket->id] ?? 0));
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
