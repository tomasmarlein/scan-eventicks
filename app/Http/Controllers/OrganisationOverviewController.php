<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Illuminate\Http\Request;

class OrganisationOverviewController extends Controller
{
    public function index($slug)
    {
        $organisation = Organisation::with('events')
                                    ->where('slug', $slug)
                                    ->first();

        if (!$organisation instanceof Organisation) {
            abort(404);
        }

        $events = $organisation->events()->orderBy('start', 'desc')->get();

        return view('web.overview', [
            'organisation' => $organisation,
            'events'       => $events,
        ]);
    }
}
