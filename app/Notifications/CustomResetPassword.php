<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends BaseResetPassword
{
    /**
     * Construye el mensaje de correo electrónico para el restablecimiento de contraseña.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Puedes personalizar el enlace si es necesario. En este ejemplo se usa la URL predeterminada.
        return (new MailMessage)
            ->subject('Restablece tu contraseña en ' . config('app.name'))
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Restablecer Contraseña', url(config('app.url') . route('password.reset', $this->token, false)))
            ->line('Este enlace expira en ' . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') . ' minutos.')
            ->line('Si no solicitaste el restablecimiento de contraseña, no se requiere ninguna acción.')
            ->salutation('Saludos, ' . config('app.name'));
    }
}
