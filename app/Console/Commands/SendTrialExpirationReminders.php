<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use App\Notifications\TrialExpirationReminderEmail;

class SendTrialExpirationReminders extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'trial:send-expiration-reminders';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Enviar recordatorios de expiración de prueba gratuita a los usuarios cuya prueba expira pronto.';

    /**
     * Ejecuta el comando.
     *
     * @return int
     */
    public function handle()
    {
        // Establecemos un umbral: recordamos a los usuarios 3 días antes de la expiración
        $threshold = Carbon::now()->addDays(3);

        // Obtenemos los usuarios que tengan definida la fecha de expiración de prueba (trial_expires_at)
        // y cuya fecha de expiración sea menor o igual al umbral, pero aún no haya pasado
        $users = User::whereNotNull('trial_expires_at')
            ->where('trial_expires_at', '<=', $threshold)
            ->where('trial_expires_at', '>', Carbon::now())
            ->get();

        foreach ($users as $user) {
            $user->notify(new TrialExpirationReminderEmail($user->trial_expires_at));
        }

        $this->info('Recordatorios de expiración de prueba gratuita enviados correctamente.');
        return 0;
    }
}
