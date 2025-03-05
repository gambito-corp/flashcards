<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionConfirmationEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Datos del usuario y de la suscripción.
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Datos de la suscripción.
     *
     * @var object
     */
    protected $subscription;

    /**
     * Crea una nueva instancia de notificación.
     *
     * @param  \App\Models\User  $user
     * @param  object  $subscription
     * @return void
     */
    public function __construct($user, $subscription)
    {
        $this->user = $user;
        $this->subscription = $subscription;
    }

    /**
     * Obtiene los canales de entrega de la notificación.
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
            ->subject('Confirmación de tu suscripción en ' . config('app.name'))
            ->greeting('Hola ' . $this->user->name . ',')
            ->line('¡Gracias por adquirir la suscripción "' . $this->subscription->product->name . '"!')
            ->line('A continuación, te detallamos la información de tu suscripción:')
            ->line('**Plan:** ' . $this->subscription->product->name)
            ->line('**Precio:** $' . number_format($this->subscription->product->price, 2))
            ->line('**Duración:** ' . $this->subscription->product->duration_days . ' dias')
            ->action('Acceder a tu cuenta', url('/dashboard'))
            ->line('Si tienes alguna duda o necesitas ayuda, contáctanos.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Representación de la notificación en formato array.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'subscription_id' => $this->subscription->id,
        ];
    }
}
