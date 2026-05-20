<?php

use App\Http\Controllers\ManuelController;
use App\Http\Controllers\OrganisationOverviewController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('login'));
})->name('home');

Route::middleware('auth')->prefix('scan')->group(function () {
    Route::get('/overzicht', [OverviewController::class, 'index'])->name('dashboard');

    Route::get('/organisation/{slug}', [OrganisationOverviewController::class, 'index'])->name('organisation.overview');
    Route::get('/organisation/{org_slug}/event/{slug}', [OverviewController::class, 'event'])->name('event.overview');

    Route::get('/organisation/{org_slug}/event/{slug}/manueel', [ManuelController::class, 'index'])->name('scan.manuel');
    Route::get('/organisation/{org_slug}/event/{slug}/tickets/search', [ManuelController::class, 'search'])->middleware('throttle:scan-search')->name('events.tickets.search');

    Route::post('/organisation/{org_slug}/events/{slug}/ticket/{orderline_uuid}/check-in', [ManuelController::class, 'checkin'])->name('manuel.checkin');
    Route::post('/organisation/{org_slug}/events/{slug}/ticket/{orderline_uuid}/check-uit', [ManuelController::class, 'checkout'])->name('manuel.checkout');

    Route::get('/organisation/{org_slug}/event/{slug}/tickets', [ScanController::class, 'index'])->name('scan.tickets');
    Route::post('/organisation/{org_slug}/event/{slug}/camera', [ScanController::class, 'camera'])->name('scan.camera');
    Route::post('/organisation/{org_slug}/scan/result/{event_slug}', [ScanController::class, 'store'])->middleware('throttle:scan-submit')->name('scan.result');
});

require __DIR__.'/auth-custom.php';
