<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class RenewalReminderEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Registro de la compra.
     *
     * @var \App\Models\Purchase
     */
    protected $purchase;

    /**
     * Fecha de la próxima renovación.
     *
     * @var \Carbon\Carbon
     */
    protected $renewalDate;

    /**
     * Crea una nueva instancia de notificación.
     *
     * @param  \App\Models\Purchase  $purchase
     * @param  \Carbon\Carbon  $renewalDate
     * @return void
     */
    public function __construct($purchase, Carbon $renewalDate)
    {
        $this->purchase = $purchase;
        $this->renewalDate = $renewalDate;
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
            ->subject('Recordatorio: Renovación próxima de tu suscripción en ' . config('app.name'))
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Te recordamos que tu suscripción al plan "' . $product->name . '" se renovará el ' . $this->renewalDate->format('d/m/Y') . '.')
            ->line('**Monto a cobrar:** $' . number_format($product->price, 2))
            ->action('Administrar suscripción', url('/subscription'))
            ->line('Si deseas actualizar tu método de pago o cancelar tu suscripción, ingresa a tu cuenta antes de la fecha de renovación.')
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
            'purchase_id' => $this->purchase->id,
            'renewal_date' => $this->renewalDate->toDateTimeString(),
        ];
    }
}
