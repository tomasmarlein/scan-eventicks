<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesScannerContext;
use App\Models\Event;
use App\Models\Orderline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManuelController extends Controller
{
    use ResolvesScannerContext;

    public function index(string $org_slug, string $slug)
    {
        $organisation = $this->resolveOrganisation($org_slug);
        $event = $this->resolveEventForOrganisation($organisation, $slug, ['tickets'], ['id', 'uuid', 'organisation_id', 'name', 'slug']);

        $limit = (int) config('scanner.manual_list_limit', 100);
        $orderlines = $this->orderlineListQuery($event)
            ->orderByDesc('id')
            ->limit($limit + 1)
            ->get();

        $hasMore = $orderlines->count() > $limit;
        $orderlines = $orderlines
            ->take($limit)
            ->map(fn (Orderline $orderline) => $orderline->toScannerListItem($org_slug, $slug))
            ->all();

        return view('web.manuel', [
            'organisation' => $organisation,
            'event' => $event,
            'orderlines' => $orderlines,
            'manualLimit' => $limit,
            'hasMoreOrderlines' => $hasMore,
        ]);
    }

    public function search(string $org_slug, string $slug, Request $request)
    {
        $organisation = $this->resolveOrganisation($org_slug);
        $event = $this->resolveEventForOrganisation($organisation, $slug, [], ['id', 'uuid', 'organisation_id', 'name', 'slug']);

        $q = trim((string) $request->input('q', ''));
        $limit = (int) config('scanner.manual_search_limit', 75);

        $orderlines = $this->orderlineListQuery($event)
            ->when($q !== '', fn ($query) => $query->search($q))
            ->orderByDesc('id')
            ->limit($limit + 1)
            ->get();

        $hasMore = $orderlines->count() > $limit;
        $orderlines = $orderlines
            ->take($limit)
            ->map(fn (Orderline $orderline) => $orderline->toScannerListItem($org_slug, $slug));

        $html = view('web.partials.orderlines-list', [
            'orderlines' => $orderlines,
            'event' => $event,
        ])->render();

        return response()->json([
            'html' => $html,
            'count' => $orderlines->count(),
            'has_more' => $hasMore,
            'limit' => $limit,
        ]);
    }

    public function checkin(string $org_slug, string $slug, string $orderline_uuid)
    {
        $organisation = $this->resolveOrganisation($org_slug);
        $event = $this->resolveEventForOrganisation($organisation, $slug, [], ['id', 'uuid', 'organisation_id', 'name', 'slug']);

        [$type, $message] = DB::connection((new Orderline)->getConnectionName())->transaction(function () use ($event, $orderline_uuid) {
            $orderline = Orderline::query()
                ->forEvent((int) $event->id)
                ->matchingQr($orderline_uuid)
                ->lockForUpdate()
                ->firstOrFail();

            if ((bool) ($orderline->blocked ?? false)) {
                return ['error', 'Dit ticket is geblokkeerd.'];
            }

            if ((bool) ($orderline->scanned ?? false)) {
                return ['info', 'Dit ticket is al ingecheckt.'];
            }

            $orderline->forceFill(['scanned' => true])->save();
            $this->incrementScannerCount();

            return ['success', 'Ticket is succesvol ingecheckt.'];
        }, 3);

        smilify($type, $message);

        return redirect()->route('scan.manuel', ['org_slug' => $org_slug, 'slug' => $slug]);
    }

    public function checkout(string $org_slug, string $slug, string $orderline_uuid)
    {
        $organisation = $this->resolveOrganisation($org_slug);
        $event = $this->resolveEventForOrganisation($organisation, $slug, [], ['id', 'uuid', 'organisation_id', 'name', 'slug']);

        [$type, $message] = DB::connection((new Orderline)->getConnectionName())->transaction(function () use ($event, $orderline_uuid) {
            $orderline = Orderline::query()
                ->forEvent((int) $event->id)
                ->matchingQr($orderline_uuid)
                ->lockForUpdate()
                ->firstOrFail();

            if (! (bool) ($orderline->scanned ?? false)) {
                return ['info', 'Dit ticket is al uitgecheckt.'];
            }

            $orderline->forceFill(['scanned' => false])->save();

            return ['success', 'Ticket is succesvol uitgecheckt.'];
        }, 3);

        smilify($type, $message);

        return redirect()->route('scan.manuel', ['org_slug' => $org_slug, 'slug' => $slug]);
    }


    private function incrementScannerCount(): void
    {
        try {
            auth()->user()?->increment('scan_count');
        } catch (\Throwable) {
            // A missing optional counter may not block manual check-in.
        }
    }

    private function orderlineListQuery(Event $event)
    {
        $query = Orderline::query()->forEvent((int) $event->id);
        $columns = Orderline::scannerSelectColumns();

        if ($columns !== []) {
            $query->select($columns);
        }

        return $query;
    }
}
