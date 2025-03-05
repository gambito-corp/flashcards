<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends BaseVerifyEmail
{

    protected $plainPassword;

    public function __construct($plainPassword = null)
    {
        $this->plainPassword = $plainPassword;
    }
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        $mailMessage = (new MailMessage)
            ->subject('Verifica tu correo electrónico')
            ->greeting('¡Hola!')
            ->line('Por favor, haz clic en el botón de abajo para verificar tu dirección de correo.');

        // Si se indicó que la contraseña fue generada, la incluimos en el correo.
        if ($this->plainPassword) {
            $mailMessage->line("Tu contraseña es: {$this->plainPassword}");
        }

        $mailMessage->action('Verificar correo', $verificationUrl)
            ->line('Si no creaste una cuenta, no es necesario realizar ninguna acción.');

        return $mailMessage;
    }
}
