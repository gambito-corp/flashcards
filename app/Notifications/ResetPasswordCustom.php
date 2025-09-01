<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordCustom extends Notification
{
    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Cambia la URL por la de tu frontend
        $url = env('FRONTEND_URL', 'http://localhost:3000') . "/reset-password?token={$this->token}&email={$this->email}";

        return (new MailMessage)
            ->subject('Restablece tu contraseña')
            ->line('Recibimos una solicitud para restablecer tu contraseña.')
            ->action('Restablecer contraseña', $url)
            ->line('Si no solicitaste este cambio, puedes ignorar este correo.');
    }

}
