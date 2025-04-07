<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class suscipcionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "payer_email" => $this->getEmail(),
            "back_url" => config('app.url') . 'dashboard', // URL a redirigir después del pago
            "reason" =>  $this->description, // Descripción de la suscripción
            "auto_recurring" => [
                "frequency" => 1, // Cada 1 mes
                "frequency_type" => "months", // Tipo de frecuencia
                "transaction_amount" =>  $this->getPrice(), // Monto a cobrar
                "currency_id" => "PEN", // Moneda del cobro
                "start_date" => now(),
            ],
            'metadata' => [
                'plan_id'    =>  $this->id,
                'plan_name'  =>  $this->name,
                'plan_price' =>  $this->price,
                'user_id'    => Auth()->user()->id,
            ],
            "external_reference" => $this->externalReference(),
            "auto_return"        => "approved",
            "notification_url"   => config('app.url') . 'webhooks/mercadopago'
        ];
    }



    private function getEmail()
    {
        return config('services.mercadopago.sandbox')
            ? "test_user_973277161@testuser.com"
            : Auth()->user()->email;
    }

    private function getPrice()
    {
        return config('services.mercadopago.sandbox')
            ? 3
            : $this->price;
    }

    private function externalReference()
    {
        return $this->id . '_' . Auth::user()->email;
    }
}
