<?php
/**
 * SubscriptionService.php
 *
 * Servicio que encapsula la lógica de Mercado Pago (suscripciones
 * con plan y sin plan) + persistencia propia.
 */

namespace App\Services\Api\Pagos;

use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preapproval\PreapprovalClient;
use MercadoPago\Client\PreapprovalPlan\PreapprovalPlanClient;
use MercadoPago\Exceptions\MPExceptions;

class SubscriptionService
{
    public function __construct(
        protected PreapprovalClient     $preClient,
        protected PreapprovalPlanClient $planClient
    )
    {
        // MercadoPago\SDK::setAccessToken(config('services.mercadopago.token'));
    }

    /**
     * Crea una suscripción con plan o sin plan.
     */
    public function createSubscription(
        User  $user,
        bool  $isPlan,
        ?int  $productId = null,
        array $planMeta = []
    ): Subscription
    {
        return DB::transaction(function () use ($user, $isPlan, $productId, $planMeta) {
            if ($isPlan) {
                $planId = $this->createOrGetPlan($planMeta);
                $mpSub = $this->preClient->create([
                    'preapproval_plan_id' => $planId,
                    'payer_email' => $user->email,
                    'card_token_id' => $planMeta['card_token'] ?? null,
                    'reason' => $planMeta['plan_name']
                ]);
            } else {
                $product = Product::findOrFail($productId);
                $mpSub = $this->preClient->create([
                    'reason' => $product->name,
                    'auto_recurring' => [
                        'transaction_amount' => (float)$product->price,
                        'currency_id' => 'PEN',
                        'frequency' => 1,
                        'frequency_type' => $product->duration_days >= 180 ? 'months' : 'months',
                    ],
                    'back_url' => $product->url ?? config('app.url'),
                    'payer_email' => $user->email,
                ]);
            }

            return Subscription::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'mercadopago_id' => $mpSub->id,
                'preapproval_plan_id' => $mpSub->preapproval_plan_id ?? null,
                'status' => $mpSub->status,
                'init_point' => $mpSub->init_point,
                'frequency' => $mpSub->auto_recurring->frequency ?? 1,
                'frequency_type' => $mpSub->auto_recurring->frequency_type ?? 'months',
                'transaction_amount' => $mpSub->auto_recurring->transaction_amount ?? null,
            ]);
        });
    }

    /**
     * Crea (o reutiliza) un plan en Mercado Pago y devuelve el ID.
     */
    private function createOrGetPlan(array $meta): string
    {
        // Si ya existe en BD lo devuelves, simplificado:
        $plan = Subscription::where('plan_reference', $meta['plan_name'])->first();
        if ($plan?->preapproval_plan_id) {
            return $plan->preapproval_plan_id;
        }

        $mpPlan = $this->planClient->create([
            'reason' => $meta['plan_name'],
            'back_url' => config('app.url'),
            'auto_recurring' => [
                'frequency' => $meta['freq'],
                'frequency_type' => 'months',
                'transaction_amount' => $meta['amount'],
                'currency_id' => 'PEN'
            ]
        ]);

        // Persistir la referencia local si lo deseas
        return $mpPlan->id;
    }

    /**
     * Cancela la suscripción en Mercado Pago y en BD.
     */
    public function cancelSubscription(Subscription $sub): bool
    {
        try {
            $this->preClient->update($sub->mercadopago_id, ['status' => 'cancelled']);
            $sub->update(['status' => 'cancelled']);
            return true;
        } catch (MPExceptions $e) {
            Log::error('[MP] Error al cancelar suscripción', ['id' => $sub->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Procesa eventos del webhook y actualiza la suscripción.
     */
    public function handleWebhook(array $payload): bool
    {
        if (($payload['type'] ?? '') !== 'preapproval') {
            return false;
        }

        $sub = Subscription::where('mercadopago_id', $payload['data']['id'])->first();
        if (!$sub) {
            return false;
        }

        $sub->update(['status' => $payload['action']]);
        return true;
    }

    public function getPlans()
    {
        return Product::whereNotNull('url')->get();
    }
}
