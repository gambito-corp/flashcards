<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    protected MercadoPagoService $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService){$this->mercadoPagoService = $mercadoPagoService;}
    public function planes()
    {
        $planes = Product::query()->take(2)->get();
        return view('index.planes', compact('planes'));
    }

    public function gettigPay($productId)
    {
        $user = Auth::user();

        $plan = Product::findOrFail($productId);
        try {
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'product_id' => $plan->id,
                'purchased_at' => now(),
                'status' => 'pending',
                'external_reference' => "$user->email",
            ]);

            if (!$purchase) {
                Log::error('No se pudo crear la purchase.');
                return redirect()->back()->with('error', 'No se pudo crear la compra.');
            }

            return redirect($plan->url);

        } catch (\Exception $e) {
            Log::error('Error al crear purchase: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error interno al crear la compra.');
        }
    }


    public function createSubscription(Product $product)
    {
        $this->mercadoPagoService->getPreapproval($product);
        $this->mercadoPagoService->checkSuscription();

        try {
            $this->mercadoPagoService->purchase
                ? $this->mercadoPagoService->updatePurchase($this->mercadoPagoService->purchase)
                : $this->mercadoPagoService->createPurchase();

            return redirect()->away($this->mercadoPagoService->subscription->init_point);
        } catch (\Exception $e) {
            $action = $this->mercadoPagoService->purchase ? 'actualizar' : 'crear';
            return response()->json([
                'success' => false,
                'message' => "Error al $action la suscripción."
            ], 500);
        }
    }


}
