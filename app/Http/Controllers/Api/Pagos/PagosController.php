<?php

namespace App\Http\Controllers\Api\Pagos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Pagos\Subscription\StoreSubscriptionRequest;
use App\Http\Resources\Api\Pagos\SubscriptionResource;
use App\Models\Subscription;
use App\Services\Api\Pagos\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use MercadoPago\Exceptions\MPApiException;

class PagosController extends Controller
{
    /** @var SubscriptionService */
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->middleware('auth:sanctum');
        $this->subscriptionService = $subscriptionService;
    }


    /**
     * GET /api/subscriptions/plans
     * Lista los planes de suscripción disponibles.
     *
     * @return Response
     */
    public function plans(): Response
    {
        $plans = $this->subscriptionService->getPlans();

        return response($plans, 200);
    }

    /**
     * GET /api/subscriptions
     * Lista las suscripciones activas del usuario autenticado.
     *
     * @return Response
     */
    public function index(): Response
    {
        $user = auth()->user();
        $subs = Subscription::where('user_id', $user->id)->latest()->get();

        return response(SubscriptionResource::collection($subs), 200);
    }

    /**
     * POST /api/subscriptions
     * Crea una suscripción (con plan o sin plan).
     *
     * @param StoreSubscriptionRequest $request
     * @return Response
     */
    public function store(StoreSubscriptionRequest $request): Response
    {
        try {
            $user = $request->user();

            // Service layer decide qué flujo usar según el payload
            $subscription = $this->subscriptionService->createSubscription(
                user: $user,
                isPlan: $request->boolean('use_plan'),
                productId: $request->input('product_id'),
                planMeta: $request->only(['plan_name', 'freq', 'amount'])
            );

            return response(new SubscriptionResource($subscription), 201);
        } catch (MPApiException $e) {

            // 🔎 1. Datos crudos que envió Mercado Pago
            Log::error('[MP] createSubscription error', [
                'status' => $e->getApiResponse()->getStatusCode(),
                'contents' => $e->getApiResponse()->getContent()    // JSON completo
            ]);

            // 🔎 2. Muestra algo legible en desarrollo
            throw new \RuntimeException(
                $e->getApiResponse()->getContent()['message']
                ?? 'Error API Mercado Pago'
            );
        } catch (ValidationException $e) {
            return response([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            Log::error('[MP] Error al crear suscripción', ['error' => $e->getMessage()]);
            return response([
                'success' => false,
                'message' => 'Error interno'
            ], 500);
        }
    }

    /**
     * GET /api/subscriptions/{id}
     * Muestra detalles de una suscripción.
     */
    public function show(Subscription $subscription): Response
    {
        $this->authorize('view', $subscription);

        return response(new SubscriptionResource($subscription), 200);
    }

    /**
     * DELETE /api/subscriptions/{id}
     * Cancela una suscripción.
     */
    public function destroy(Subscription $subscription): Response
    {
        $this->authorize('delete', $subscription);

        $canceled = $this->subscriptionService->cancelSubscription($subscription);

        return response([
            'success' => $canceled,
            'message' => $canceled
                ? 'Suscripción cancelada'
                : 'No se pudo cancelar'
        ], $canceled ? 200 : 500);
    }

    /**
     * POST /api/mercadopago/webhook
     * Webhook: procesa eventos de Mercado Pago.
     */
    public function webhook(Request $request)
    {
        // 1. Verificar firma (opcional, recomendado)
        $secret = env('MP_SECRET_KEY');           // tu clave secreta de MP
        $sent = $request->header('x-signature'); // llega como hmac sha256

        if ($secret && !hash_equals(
                $sent ?? '',
                hash_hmac('sha256', $request->getContent(), $secret)
            )) {
            return response('firma inválida', 403);
        }

        // 2. Procesar evento
        $type = $request->input('type');   // payment | preapproval
        $action = $request->input('action'); // payment.created | preapproval.authorized …

        match ("{$type}.{$action}") {
            'preapproval.authorized' => $this->handleAuthorized($request),
            'payment.created' => $this->handlePayment($request),
            default => null
        };

        // 3. Devolver 200 para que MP no reintente
        return response('ok', 200);
    }
}
