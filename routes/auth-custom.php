<?php

use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordController;
use Devdojo\Auth\Http\Controllers\LogoutController;
use Devdojo\Auth\Http\Controllers\SocialController;
use Devdojo\Auth\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// Create redirect routes for common authentication routes

Route::redirect('login', 'auth/login')->name('login');
Route::redirect('register', 'auth/register')->name('register');

// define the logout route
Route::middleware(['auth', 'web'])->group(function () {

    Route::post('/auth/logout', LogoutController::class)
         ->name('logout');

    Route::redirect('logout', '/auth/logout')->name('logout');

    Route::get('/auth/logout', [LogoutController::class, 'getLogout'])->name('logout.get');

});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
         ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
         ->middleware(['signed', 'throttle:6,1'])
         ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
         ->middleware('throttle:6,1')
         ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
         ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
});

Route::middleware(['web'])->group(function () {
    // Add social routes
    Route::get('auth/{driver}/redirect', [SocialController::class, 'redirect']);
    Route::get('auth/{driver}/callback', [SocialController::class, 'callback']);

});
