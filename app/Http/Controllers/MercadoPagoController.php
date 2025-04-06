<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\MercadoPagoService;

class MercadoPagoController extends Controller
{
    protected MercadoPagoService $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService){$this->mercadoPagoService = $mercadoPagoService;}
    public function planes()
    {
        $planes = Product::query()->take(2)->get();
        return view('index.planes', compact('planes'));
    }
    public function createSubscription(Product $product)
    {
        $this->mercadoPagoService->getPreapproval($product);

        $hasSubscription = $this->mercadoPagoService->checkSuscription();

        try {
            $hasSubscription
                ? $this->mercadoPagoService->updatePurchase($hasSubscription)
                : $this->mercadoPagoService->createPurchase();

            return redirect()->away($this->mercadoPagoService->subscription->init_point);
        } catch (\Exception $e) {
            $action = $hasSubscription ? 'actualizar' : 'crear';
            return response()->json([
                'success' => false,
                'message' => "Error al $action la suscripción."
            ], 500);
        }
    }


}
