<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Usuario para personalizar el correo.
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Crea una nueva instancia de notificación.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Obtiene los canales de entrega de la notificación.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Construye el mensaje de correo electrónico.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('¡Bienvenido a ' . config('app.name') . '!')
            ->greeting('¡Hola ' . $this->user->name . '!')
            ->line('Gracias por registrarte en nuestra plataforma.')
            ->line('Estamos encantados de tenerte con nosotros y queremos ayudarte a comenzar.')
            ->action('Ir a tu panel', url('/dashboard'))
            ->line('Si tienes alguna pregunta, no dudes en contactarnos.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Representación de la notificación en formato array.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
