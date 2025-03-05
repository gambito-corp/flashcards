<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HolaMundoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crea una nueva instancia del correo.
     *
     * @return void
     */
    public function __construct()
    {
        // Puedes pasar datos si es necesario.
    }

    /**
     * Construye el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Hola Mundo')
            ->markdown('emails.hola_mundo');
    }
}
