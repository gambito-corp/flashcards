<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class SubscriptionCancellationEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Registro de la compra asociado a la suscripción que se cancela.
     *
     * @var \App\Models\Purchase
     */
    protected $purchase;

    /**
     * Crea una nueva instancia de notificación.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return void
     */
    public function __construct($purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * Determina los canales de entrega de la notificación.
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
        // Calculamos la fecha en la que se terminará la suscripción:
        $expirationDate = Carbon::parse($this->purchase->purchased_at)
            ->addDays($this->purchase->product->duration_days)
            ->format('d/m/Y');

        return (new MailMessage)
            ->subject('Cancelación de tu suscripción en ' . config('app.name'))
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Hemos recibido tu solicitud de cancelación de la suscripción al plan "' . $this->purchase->product->name . '".')
            ->line('Tu suscripción se cancelará y dejarás de tener acceso al servicio a partir del ' . $expirationDate . '.')
            ->line('Si esto fue un error o necesitas ayuda, por favor contáctanos para asistirte.')
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
        $expirationDate = Carbon::parse($this->purchase->purchased_at)
            ->addDays($this->purchase->product->duration_days)
            ->toDateTimeString();

        return [
            'purchase_id'       => $this->purchase->id,
            'cancellation_date' => $expirationDate,
        ];
    }
}
