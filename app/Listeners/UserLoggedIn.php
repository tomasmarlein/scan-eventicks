<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserLoggedIn
{
    /**
     * @throws ValidationException
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! (bool) ($user->active ?? false)) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Je account is niet actief.',
            ]);
        }
    }
}
