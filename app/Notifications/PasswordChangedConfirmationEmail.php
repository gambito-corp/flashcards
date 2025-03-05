<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChangedConfirmationEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Crea una nueva instancia de la notificación.
     *
     * @return void
     */
    public function __construct()
    {
        // Puedes recibir datos adicionales si lo necesitas.
    }

    /**
     * Define los canales de entrega.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Construye el mensaje de correo electrónico.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Confirmación de cambio de contraseña en ' . config('app.name'))
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Te confirmamos que tu contraseña ha sido actualizada exitosamente.')
            ->line('Si no realizaste este cambio, por favor contacta a nuestro equipo de soporte de inmediato.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Representa la notificación en formato array.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            // Puedes agregar información adicional si lo necesitas.
        ];
    }
}
