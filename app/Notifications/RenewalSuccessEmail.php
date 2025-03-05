<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class RenewalSuccessEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Registro de la compra (renovación).
     *
     * @var \App\Models\Purchase
     */
    protected $purchase;

    /**
     * Nueva fecha de renovación.
     *
     * @var \Carbon\Carbon
     */
    protected $newRenewalDate;

    /**
     * Crea una nueva instancia de notificación.
     *
     * @param  \App\Models\Purchase  $purchase
     * @param  \Carbon\Carbon  $newRenewalDate
     * @return void
     */
    public function __construct($purchase, Carbon $newRenewalDate)
    {
        $this->purchase = $purchase;
        $this->newRenewalDate = $newRenewalDate;
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
        $product = $this->purchase->product;
        return (new MailMessage)
            ->subject('Renovación exitosa de tu suscripción en ' . config('app.name'))
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Tu suscripción al plan "' . $product->name . '" ha sido renovada exitosamente.')
            ->line('**Monto cobrado:** $' . number_format($product->price, 2))
            ->line('Tu nueva fecha de renovación es: ' . $this->newRenewalDate->format('d/m/Y'))
            ->action('Ir a tu cuenta', url('/dashboard'))
            ->line('Gracias por confiar en nosotros.')
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
            'purchase_id'      => $this->purchase->id,
            'new_renewal_date' => $this->newRenewalDate->toDateTimeString(),
        ];
    }
}
