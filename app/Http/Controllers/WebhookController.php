<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class WebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        Log::info(json_encode($request->all()));
//        if ($request->post('type') == 'payment'){
//            MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
//            $client = new PaymentClient();
//
//            $payment = $client->get($request->get('data_id'));
//            Log::info(json_encode($payment));
//            if ($payment->status == 'approved') {
//                //Guardamos la info de la suscripcion...
//
//                Log::info(json_encode($payment));
//
//
//
//            }
//
//
//        }
    }
}
