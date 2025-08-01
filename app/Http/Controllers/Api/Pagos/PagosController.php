<?php

namespace App\Http\Controllers\Api\Pagos;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Services\Api\Pagos\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class PagosController extends Controller
{
    /** @var SubscriptionService */
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->middleware('auth:sanctum')->except([
            'crearPlan',
            'crearSuscripcion',
            'handle',
        ]);
        $this->subscriptionService = $subscriptionService;
    }


    public function plans()
    {
        return $this->subscriptionService->getPlanesSuscripcion();
    }

    public function crearPlan(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string',
            'precio' => 'required|numeric|min:1',
            'frecuencia' => 'sometimes|integer|min:1',
            'frecuencia_tipo' => 'sometimes|string|in:months,days,years',
            'description' => 'sometimes|string',
            'referencia' => 'sometimes|string',
            'url' => 'sometimes|url',
            'duration_days' => 'sometimes|integer|min:1',
        ]);

        $planData = $this->subscriptionService->crearPlanSuscripcion(
            $validated['nombre'],
            $validated['precio'],
            $validated['frecuencia'] ?? 1,
            $validated['frecuencia_tipo'] ?? 'months'
        );

        if (!isset($planData['id'])) {
            return response()->json(['error' => 'No se pudo crear el plan en Mercado Pago'], 500);
        }

        // Guardar o actualizar producto local con el id que devuelve Mercado Pago
        $product = Product::updateOrCreate(
            ['name' => $validated['nombre']], // condición para encontrar
            [
                'price' => $validated['precio'],
                'duration_days' => $validated['duration_days'] ?? 30,
                'description' => $validated['description'] ?? null,
                'referencia' => $validated['referencia'] ?? null,
                'url' => $planData['init_point'] ?? null,
                'mp_preapproval_plan_id' => $planData['id'], // guarda el id real Mercado Pago
            ]
        );

        return response()->json([
            'mensaje' => 'Plan creado y guardado correctamente',
            'plan_local_id' => $product->id,
            'mp_preapproval_plan_id' => $planData['id'],
            'detalle' => $planData,
        ]);
    }


    public function crearSuscripcion(Request $request): Response
    {
        $validated = $request->validate([
            'plan_id' => 'required',
            'payer_email' => 'required|email',
            'external_reference' => 'required|int|exists:users,id',
            'card_token_id' => 'required|string|max:255',
        ]);

        $subscription = $this->subscriptionService->crearSuscripcion(
            $validated['plan_id'],
            $validated['payer_email'],
            $validated['external_reference'] ?? null,
            $validated['card_token_id']
        );
        return response($subscription, 201);
    }

    public function handle(Request $request)
    {
        \Log::error('Webhook MercadoPago recibido:', $request->all());

        $type = $request->input('type');
        $preapprovalId = $request->input('data.id');

        if ($type === 'subscription_preapproval' && $preapprovalId) {
            $accessToken = config('services.mercadopago.access_token');
            $response = Http::withToken($accessToken)
                ->get("https://api.mercadopago.com/preapproval/{$preapprovalId}");

            $preapproval = $response->json();

            \Log::info('Detalles Completos del preapproval:', $preapproval);

            $usuarioId = $preapproval['external_reference'] ?? null;

            $planPrice = $preapproval['auto_recurring']['transaction_amount'] ?? null;
            $plan = \App\Models\Product::query()
                ->where('price', $planPrice)
                ->first();

            \Log::info('External Reference recibido del preapproval:', ['usuario' => $usuarioId]);

            if ($usuarioId) {
                $usuario = \App\Models\User::query()->where('id', $usuarioId)->first();

                if ($usuario) {
                    $usuario->status = true;
                    $usuario->premium_at = now();
                    $usuario->save();

                    $compra = Purchase::query()
                        ->create(
                            [
                                'user_id' => $usuario->id,
                                'product_id' => $plan->id ?? null,
                                'purchased_at' => now(),
                                'preapproval' => $preapproval,
                                'preapproval_id' => $preapprovalId,
                                'status' => $preapproval['status'] ?? 'authorized',
                                'payer_id' => $preapproval['payer_id'] ?? null,
                                'external_reference' => $usuarioId,
                                'init_point' => $preapproval['init_point'] ?? null,
                            ]
                        );
                    \Log::info("Suscripción activada para usuario ID {$usuario->id} con preapproval_id {$preapprovalId}");
                } else {
                    \Log::warning("No se encontró usuario con ID {$usuarioId}");
                }
            } else {
                \Log::warning("No se encontró external_reference en la suscripción {$preapprovalId}");
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }


}
