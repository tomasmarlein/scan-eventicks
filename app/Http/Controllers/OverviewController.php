<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organisation;
use App\Services\ApiService;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        $organisations = [];

        $userId = auth()->user()->id;

        if (auth()->user()->hasRole('admin')) {
            $organisations = Organisation::with('users')->paginate(14);
        } else {
            $organisations = Organisation::with('users')
                                         ->whereHas('users', function ($query) use ($userId) {
                                             $query->where('user_id', $userId);
                                         })->paginate(15);
        }

        return view('web.organisations', [
            'organisations' => $organisations,
        ]);
    }

    public function event($org_slug, $slug)
    {
        /// get organisation by slug
        $organisation = Organisation::where('slug', $org_slug)->first();

        if (!$organisation) {
            abort(404);
        }

        $event = Event::with('tickets.orderlines')->where('slug', $slug)->first();

        if (!$event) {
            abort(404);
        }

        return view('web.event', [
            'organisation' => $organisation,
            'event'        => $event,
        ]);
    }
}
