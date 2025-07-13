<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends BaseVerifyEmail
{
    /** @var string|null */
    protected $plainPassword;

    public function __construct(string $plainPassword = null)
    {
        $this->plainPassword = $plainPassword;
    }

    /* ----------------------------------------------------------
       1.   Genera la URL con el dominio deseado
       ----------------------------------------------------------*/
    protected function verificationUrl($notifiable): string
    {
        // URL firmada que entiende el backend
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Sustituye el dominio por el que debe ver el usuario
        return str_replace(
            url('/'),                               // dominio actual del backend
            'https://doctormbs.medbystudents.com',  // dominio público que deseas mostrar
            $signedUrl
        );
    }

    /* ----------------------------------------------------------
       2.   Contenido del correo
       ----------------------------------------------------------*/
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        $mail = (new MailMessage)
            ->subject('Verifica tu correo electrónico')
            ->greeting('¡Hola!')
            ->line('Por favor, haz clic en el botón de abajo para verificar tu dirección de correo.')
            ->action('Verificar correo', $verificationUrl)
            ->line('Si no creaste una cuenta, no es necesario realizar ninguna acción.');

        if ($this->plainPassword) {
            $mail->line("Tu contraseña temporal es: {$this->plainPassword}");
        }

        return $mail;
    }
}
