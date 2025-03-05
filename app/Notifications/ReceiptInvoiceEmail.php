<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class ReceiptInvoiceEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Usuario destinatario.
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Registro de la compra.
     *
     * @var \App\Models\Purchase
     */
    protected $purchase;

    /**
     * Crea una nueva instancia de notificación.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Purchase  $purchase
     * @return void
     */
    public function __construct($user, $purchase)
    {
        $this->user = $user;
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
        // Formateamos la fecha de compra usando Carbon
        $purchaseDate = Carbon::parse($this->purchase->created_at)->format('d/m/Y');

        // Suponiendo que la compra tiene relación con el modelo Product:
        // Asegúrate de que en el modelo Purchase tengas definida la relación product()
        $productName = $this->purchase->product->name ?? 'Producto no definido';
        $productPrice = $this->purchase->product->price ?? 0;

        return (new MailMessage)
            ->subject('Tu recibo de compra en ' . config('app.name'))
            ->greeting('Hola ' . $this->user->name . ',')
            ->line('Gracias por tu compra. A continuación, te mostramos los detalles de tu compra:')
            ->line('**Número de compra:** ' . $this->purchase->id)
            ->line('**Fecha de compra:** ' . $purchaseDate)
            ->line('**Producto:** ' . $productName)
            ->line('**Precio:** $' . number_format($productPrice, 2))
            ->action('Ver compra', url('/purchases/' . $this->purchase->id))
            ->line('Si tienes alguna duda, contáctanos.')
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
        ];
    }
}
