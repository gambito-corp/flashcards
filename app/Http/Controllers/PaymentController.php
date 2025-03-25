<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Purchase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createSubscription(Request $request, $productId)
    {













//        // 1. Obtener el producto y el usuario
//        $product = Product::findOrFail($productId);
//        $user = $request->user();
//
//        // 2. Calcular la frecuencia (convertir días a meses)
//        $months = max(1, intval($product->duration_days / 30));
//        $frequency = 'day';
//        if (config('app.env') === 'production') {
//            $frequency = 'months';
//        }
//
//        // 3. Preparar el array de datos (payload) para la suscripción
//        $payloadArray = [
//            'payer_email'        => 'TESTUSER918753220@testuser.com',
//            'back_url'           => route('mercadopago.callback'),
//            'reason'             => 'Suscripción a ' . $product->name,
//            'external_reference' => $user->id . '-' . $product->id . '-' . time(),
//            'auto_recurring'     => [
//                'frequency'          => $months,
//                'frequency_type'     => 'months',
//                'repetition'         => 12,
//                'billing_day'        => now()->day,
//                'currency_id'        => 'PEN',
//                'transaction_amount' => (float) $product->price,
//            ],
//        ];
//
//        // 4. Crear un cliente Guzzle
//        $client = new Client();
//
//        try {
//            // 5. Enviar la petición POST a la API de MercadoPago
//            $response = $client->post('https://api.mercadopago.com/preapproval', [
//                'headers' => [
//                    'Content-Type'  => 'application/json',
//                    'Authorization' => 'Bearer ' . config('services.mercadopago.token'),
//                    'Accept'        => 'application/json',
//                ],
//                'json' => $payloadArray,
//                'timeout' => 20, // Tiempo de espera en segundos
//            ]);
//            dd('ok');
//
//
//            // 6. Verificar el código de estado de la respuesta
//            $statusCode = $response->getStatusCode();
//            if (in_array($statusCode, [200, 201])) {
//                $body = $response->getBody()->getContents();
//                $preapprovalData = json_decode($body);
//
//                // Registrar la intención de compra en tu BD (opcional)
//                Purchase::create([
//                    'user_id'    => $user->id,
//                    'product_id' => $product->id,
//                    // Puedes guardar también $preapprovalData->id si lo deseas
//                ]);
//
//                // Redirigir al usuario a la URL de aprobación (donde ingresará sus datos de pago)
//                return redirect($preapprovalData->init_point);
//            } else {
//                return back()->withErrors([
//                    'error' => 'Error al crear la suscripción: ' . $response->getBody()->getContents()
//                ]);
//            }
//        } catch (RequestException $e) {
//            dd($e);
//            Log::error('Error al crear preapproval: ' . $e->getMessage());
//            return back()->withErrors([
//                'error' => 'Error al crear la suscripción: ' . $e->getMessage()
//            ]);
//        }
    }

//    public function checkPaymentStatus($paymentId)
//    {
//        $accessToken = config('services.mercadopago.token');
//
//        $response = Http::get("https://api.mercadopago.com/v1/payments/{$paymentId}", [
//            'access_token' => $accessToken,
//        ]);
//
//        if ($response->successful()) {
//            $data = $response->json();
//            $status = $data['status'] ?? 'desconocido';
//            return $status;
//        }
//
//        return null;
//    }
//
//    public function callback(Request $request)
//    {
//        Log::info('Callback de MercadoPago recibido:', $request->all());
//        $paymentQuery = $request->query('payment_id');
//        if (!is_array($paymentQuery) || !isset($paymentQuery['preapproval_id'])) {
//            return redirect()->back()->with('error', 'No se recibió el ID de preaprobación del pago.');
//        }
//        $preapprovalId = $paymentQuery['preapproval_id'];
//        $status = $this->checkPaymentStatus($preapprovalId);
//        dd($status);
//
//        if ($status === 'approved') {
//            // Lógica para pago exitoso
//            return redirect()->route('dashboard')->with('status', 'Pago aprobado y suscripción activada.');
//            } elseif ($status === 'rejected') {
//            // Lógica para pago rechazado
//            return redirect()->route('dashboard')->with('error', 'El pago fue rechazado.');
//        } else {
//            // Otros casos, por ejemplo, pendiente
//            return redirect()->route('dashboard')->with('warning', 'El pago se encuentra pendiente de confirmación.');
//        }
//    }

}
