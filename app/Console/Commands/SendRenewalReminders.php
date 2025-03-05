<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Purchase; // Usamos el modelo Purchase
use Carbon\Carbon;
use App\Notifications\RenewalReminderEmail;

class SendRenewalReminders extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-renewal-reminders';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Enviar recordatorios de renovación a los suscriptores con fecha de renovación próxima.';

    /**
     * Ejecuta el comando.
     *
     * @return int
     */
    public function handle()
    {
        // Umbral: recordamos a los usuarios 3 días antes de la renovación
        $threshold = Carbon::now()->addDays(3);

        // Obtenemos todas las compras junto con el producto y usuario relacionados
        $purchases = Purchase::with('product', 'user')->get();

        foreach ($purchases as $purchase) {
            // Calculamos la fecha de renovación: fecha de compra + duración del producto (en días)
            $nextRenewalDate = Carbon::parse($purchase->purchased_at)
                ->addDays($purchase->product->duration_days);

            // Si la fecha de renovación está dentro del umbral (y aún no ha pasado)
            if ($nextRenewalDate <= $threshold && $nextRenewalDate > Carbon::now()) {
                $purchase->user->notify(new RenewalReminderEmail($purchase, $nextRenewalDate));
            }
        }

        $this->info('Recordatorios de renovación enviados correctamente.');
        return 0;
    }
}
