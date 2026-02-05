<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Orderline;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class ManuelController extends Controller
{
    public function index($org_slug, $slug)
    {
        $organisation = Organisation::where('slug', $org_slug)->first();

        abort_if(!$organisation, 404);

        $event = Event::with('tickets.orderlines')->where('slug', $slug)->first();

        abort_if(!$event, 404);

        $orderlines = Orderline::where('event_id', $event['id'])->get() ?? [];

        // voeg actie-URLs toe
        $orderlines = $orderlines->map(function (Orderline $ol) use ($org_slug, $slug) {
            $orderline_uuid = $ol->orderline_uuid ?? $ol->uuid;

            return array_merge($ol->toArray(), [
                'url_checkin'  => route('manuel.checkin', ['org_slug' => $org_slug, 'slug' => $slug, 'orderline_uuid' => $orderline_uuid]),
                'url_checkout' => route('manuel.checkout', ['org_slug' => $org_slug, 'slug' => $slug, 'orderline_uuid' => $orderline_uuid]),
            ]);
        })->all();

        return view('web.manuel', [
            'organisation' => $organisation,
            'event'      => $event,
            'orderlines' => $orderlines,
        ]);
    }

    public function search($org_slug, $slug, Request $request)
    {
        $organisation = Organisation::where('slug', $org_slug)->first();
        abort_if(!$organisation, 404);

        $event = Event::where('slug', $slug)->first();
        abort_if(!$event, 404);

        $q = trim((string) $request->input('q', ''));

        // Orderline zit op tickets_mysql
        $query = Orderline::query()->where('event_id', $event->id);

        if ($q !== '') {
            $conn = (new Orderline)->getConnectionName() ?: config('database.default');

            // Alleen kolommen gebruiken die echt bestaan
            $columns = collect([
                                   'uuid',
                                   'orderline_uuid',
                                   'unique_qr_code',
                                   'unique_qr_id',
                                   'name',
                                   'email',
                                   'order_reference',
                               ])->filter(fn ($col) => Schema::connection($conn)->hasColumn('orderlines', $col))
                                 ->values()
                                 ->all();

            $query->where(function ($sub) use ($q, $columns) {
                // exact match velden (als ze bestaan)
                foreach (['uuid','orderline_uuid','unique_qr_code','unique_qr_id'] as $col) {
                    if (in_array($col, $columns, true)) {
                        $sub->orWhere($col, $q);
                    }
                }

                // fuzzy velden (als ze bestaan)
                foreach (['name','email','order_reference'] as $col) {
                    if (in_array($col, $columns, true)) {
                        $sub->orWhere($col, 'like', "%{$q}%");
                    }
                }
            });
        }

        $orderlines = $query->orderByDesc('id')->get();

        $orderlines = $orderlines->map(function (Orderline $ol) use ($org_slug, $slug) {
            $orderline_uuid = $ol->orderline_uuid ?? $ol->uuid;

            return array_merge($ol->toArray(), [
                'unique_qr_code' => $ol->unique_qr_code ?? $ol->unique_qr_id ?? $ol->uuid,
                'url_checkin'  => route('manuel.checkin', ['org_slug' => $org_slug, 'slug' => $slug, 'orderline_uuid' => $orderline_uuid]),
                'url_checkout' => route('manuel.checkout', ['org_slug' => $org_slug, 'slug' => $slug, 'orderline_uuid' => $orderline_uuid]),
            ]);
        });

        $html = view('web.partials.orderlines-list', [
            'orderlines' => $orderlines,
            'event'      => $event,
        ])->render();

        return response()->json([
                                    'html'  => $html,
                                    'count' => $orderlines->count(),
                                ]);
    }

    public function checkin($org_slug, $slug, $orderline_uuid)
    {
        $organisation = Organisation::where('slug', $org_slug)->first();
        abort_if(!$organisation, 404);

        $event = Event::with('tickets.orderlines')->where('slug', $slug)->first();
        abort_if(!$event, 404);

        // get orderline
        $orderline = Orderline::where('uuid', $orderline_uuid)->first();
        abort_if(!$orderline, 404);

        // Check if orderline is checked in
        if ($orderline->scanned) {
            smilify('info', 'Dit ticket is al ingecheckt.');
            return redirect()->route('scan.manuel', ['org_slug' => $org_slug, 'slug' => $slug]);
        }

        // Check in the orderline
        $orderline->scanned = 1;
        $orderline->save();

        smilify('success', 'Ticket is succesvol ingecheckt.');
        return redirect()->route('scan.manuel', ['org_slug' => $org_slug, 'slug' => $slug]);
    }

    public function checkout($org_slug, $slug, $orderline_uuid)
    {
        $organisation = Organisation::where('slug', $org_slug)->first();
        abort_if(!$organisation, 404);

        $event = Event::with('tickets.orderlines')->where('slug', $slug)->first();
        abort_if(!$event, 404);

        // get orderline
        $orderline = Orderline::where('uuid', $orderline_uuid)->first();
        abort_if(!$orderline, 404);

        // Check if orderline is checked in
        if (!$orderline->scanned) {
            smilify('info', 'Dit ticket is al uitgecheckt.');
            return redirect()->route('scan.manuel', ['org_slug' => $org_slug, 'slug' => $slug]);
        }

        // Check in the orderline
        $orderline->scanned = 0;
        $orderline->save();

        smilify('success', 'Ticket is succesvol uitgecheckt.');
        return redirect()->route('scan.manuel', ['org_slug' => $org_slug, 'slug' => $slug]);
    }
}
