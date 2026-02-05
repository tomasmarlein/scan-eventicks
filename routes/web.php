<?php

use App\Http\Controllers\ManuelController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('login'));
})->name('home');


Route::middleware('auth')->prefix('scan')->group(function () {
    Route::get('/overzicht', [OverviewController::class, 'index'])->name('dashboard');
    Route::get('/event/{uuid}', [OverviewController::class, 'event'])->name('event.overview');

    Route::get('/event/{uuid}/manueel', [ManuelController::class, 'index'])->name('scan.manuel');
    Route::get('/events/{uuid}/tickets/search', [ManuelController::class, 'search'])->name('events.tickets.search');

    Route::get('/events/{uuid}/ticket/{orderline_uuid}/check-in', [ManuelController::class, 'checkin'])->name('manuel.checkin');
    Route::get('/events/{uuid}/ticket/{orderline_uuid}/check-uit', [ManuelController::class, 'checkout'])->name('manuel.checkout');

    Route::get('/event/{uuid}/tickets', [ScanController::class, 'index'])->name('scan.tickets');
    Route::any('/event/{uuid}/camera', [ScanController::class, 'camera'])->name('scan.camera');
    Route::any('/scan/result/{event_uuid}', [ScanController::class, 'store'])->name('scan.result');
});



require __DIR__.'/auth-custom.php';
