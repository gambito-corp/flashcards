<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class TrialExpirationReminderEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Fecha en la que expira la prueba gratuita.
     *
     * @var \Carbon\Carbon
     */
    protected $trialExpiresAt;

    /**
     * Crea una nueva instancia de notificación.
     *
     * @param mixed $trialExpiresAt
     */
    public function __construct($trialExpiresAt)
    {
        // Convertimos la fecha a instancia de Carbon para facilitar el formateo
        $this->trialExpiresAt = Carbon::parse($trialExpiresAt);
    }

    /**
     * Determina los canales de entrega de la notificación.
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
            ->subject('¡Tu prueba gratuita está por expirar en ' . config('app.name') . '!')
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Queremos recordarte que tu período de prueba gratuita finaliza el ' . $this->trialExpiresAt->format('d/m/Y') . '.')
            ->line('Aprovecha estos últimos días para explorar todas las funcionalidades y beneficios de nuestra plataforma.')
            ->action('Actualizar a una suscripción', url('/subscription'))
            ->line('Si no actualizas, tu acceso se limitará tras la expiración de la prueba.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Representa la notificación en formato array.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'trial_expires_at' => $this->trialExpiresAt->toDateTimeString(),
        ];
    }
}
