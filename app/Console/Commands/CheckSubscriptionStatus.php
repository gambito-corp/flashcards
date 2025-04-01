<?php

namespace App\Console\Commands;

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
        $client = new Client();
        $accessToken = config('services.mercadopago.token');

        // Supongamos que cada usuario tiene un campo 'preapproval_id' que guarda el ID de la preaprobación.
        $users = User::query()->with('purchases')->where('status', 1)->whereHas('purchases', function ($query){
            $query->whereNotNull('preapproval_id');
        })->get();

        foreach ($users as $user) {
            try {
                $response = $client->get("https://api.mercadopago.com/preapproval/{$user->purchases?->preapproval_id}", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                // Si el estado de la preaprobación no es "authorized", cambiamos el status a 0.
                if (!isset($data['status']) || $data['status'] !== 'authorized') {
                    $user->update(['status' => 0]);
                    $this->info("Usuario {$user->id} marcado como inactivo (suscripción no autorizada).");
                }
            } catch (\Exception $e) {
                \Log::error("Error al verificar suscripción del usuario {$user->id}: " . $e->getMessage());
            }
        }
        return 0;
    }
}
