<?php

namespace App\Http\Controllers;

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
        // Get all events form this year with organisation_id 2
        $events = $this->apiService->getEventsByOrganisationId('2');

        return view('web.overview', [
            'events' => $events
        ]);
    }

    public function event($uuid)
    {
        // Get event by id
        $event = $this->apiService->getEventByUuid($uuid);

        if (!$event) {
            abort(404);
        }

        return view('web.event', [
            'event' => $event
        ]);
    }
}
