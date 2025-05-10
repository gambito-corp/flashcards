<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use Carbon\Carbon;

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

    public function handleWebhook(Request $request)
    {
        $data = $request->all();

        Log::info('Webhook recibido:', $data);

        $action = $data['action'] ?? null;
        $dateString = null;

        if ($action === 'payment.created' && isset($data['date_created'])) {
            $dateString = $data['date_created'];
        } elseif (($data['type'] ?? null) === 'subscription_preapproval' && isset($data['date'])) {
            $dateString = $data['date'];
        }

        if (!$dateString) {
            Log::error('No se encontró la fecha en el webhook.');
            return response()->json(['error' => 'Fecha no encontrada'], 400);
        }

        $mpDate = Carbon::parse($dateString);

        $purchase = Purchase::where('status', 'pending')
            ->whereBetween('purchased_at', [
                $mpDate->copy()->subMinute(),
                $mpDate->copy()->addMinute()
            ])
            ->latest()
            ->first();

        if (!$purchase) {
            Log::warning('No se encontró Purchase correspondiente al webhook.');
            return response()->json(['error' => 'Purchase no encontrado'], 404);
        }

        $purchase->status = 'authorized';
        $purchase->save();

        Log::info('Purchase actualizado a authorized.', ['purchase_id' => $purchase->id]);
    }
}
