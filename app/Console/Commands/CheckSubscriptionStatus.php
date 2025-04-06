<?php

namespace App\Console\Commands;

use App\Services\MercadoPagoService;
use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;

class CheckSubscriptionStatus extends Command
{
    protected $signature = 'subscriptions:check-status';
    protected $description = 'Verifica el estado de las suscripciones en Mercado Pago y actualiza el status del usuario.';

    public function handle()
    {
        // Supongamos que cada usuario tiene un campo 'preapproval_id' que guarda el ID de la preaprobación.
        $users = User::query()
            ->with('purchases')
            ->where('status', 1)
            ->whereHas('purchases', function ($query){
                $query->whereNotNull('preaproval_id');
            })
            ->get();

        foreach ($users as $user) {
            if (in_array($user->id, config('specialUsers.ids')))
                continue;
            try {
                $subscripcion = $user->purchases->last();
                $mercadoPago = new MercadoPagoService();
                $mercadoPago->checkCronSubscrition($subscripcion);
            } catch (\Exception $e) {
                \Log::error("Error al verificar suscripción del usuario {$user->id}: " . $e->getMessage());
            }
        }
        return 0;
    }
}
