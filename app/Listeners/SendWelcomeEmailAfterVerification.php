<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use App\Notifications\WelcomeEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeEmailAfterVerification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Maneja el evento Verified.
     *
     * @param  \Illuminate\Auth\Events\Verified  $event
     * @return void
     */
    public function handle(Verified $event)
    {
        // Envía el correo de bienvenida
        $event->user->notify(new WelcomeEmail($event->user));
    }
}
