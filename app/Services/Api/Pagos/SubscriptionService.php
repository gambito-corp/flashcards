<?php
/**
 * SubscriptionService.php
 *
 * Servicio que encapsula la lógica de Mercado Pago (suscripciones
 * con plan y sin plan) + persistencia propia.
 */

namespace App\Services\Api\Pagos;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use MercadoPago\Exceptions\MPExceptions;

//use MercadoPago\Client\PreapprovalPlan\PreapprovalPlanClient;

//use MercadoPago\Client\Preapproval\PreapprovalClient;

class SubscriptionService
{
    protected $accessToken;

    public function __construct()
    {
        $this->accessToken = config('services.mercadopago.access_token');
    }

    // Obtener planes de suscripción
    public function getPlanesSuscripcion(): array
    {
        return Product::query()->get()->toArray();

    }

    public function crearPlanSuscripcion(string $nombre, float $precio, int $frecuencia = 1, string $frecuenciaTipo = "months"): array
    {
        $data = [
            "reason" => $nombre,
            "auto_recurring" => [
                "frequency" => $frecuencia,
                "frequency_type" => $frecuenciaTipo,
                "transaction_amount" => $precio,
                "currency_id" => "PEN", // Cambia a tu moneda
            ],
            "back_url" => "https://front.flashcard.com/suscripcion-success", // Cambia esta URL por la tuya
            "status" => "active"
        ];

        $response = Http::withToken($this->accessToken)
            ->post('https://api.mercadopago.com/preapproval_plan', $data);
        // Puedes agregar manejo de errores. Por ahora devolvemos el JSON directo.
        return $response->json();
    }

    // Crear suscripción a un plan
    public function crearSuscripcion($plan_id, $payer_email, $external_reference, $card_token_id): array
    {
        $product = Product::query()
            ->where('id', $plan_id)
            ->first();
        $data = [
            "preapproval_plan_id" => $product->mp_preapproval_plan_id,
            "payer_email" => $payer_email,
            "external_reference" => $external_reference,
            "card_token_id" => $card_token_id,
        ];

        $resp = Http::withToken($this->accessToken)
            ->post('https://api.mercadopago.com/preapproval', $data);
        \Log::info('Respuesta de Mercado Pago al crear suscripción:', $resp->json());
        return $resp->json();
    }

    public function handle(Request $request)
    {

    }
}
