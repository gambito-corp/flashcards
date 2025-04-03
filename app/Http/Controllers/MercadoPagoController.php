<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoController extends Controller
{
    public function planes()
    {
        $planes = Product::query()->take(2)->get();
        return view('index.planes', compact('planes'));
    }
    public function plan(Product $product)
    {
        $preferenceId = $this->generatePreference($product)->id;
        $preference = $this->generatePreference($product);
        return view('index.plan', compact('product', 'preferenceId', 'preference'));
    }
    protected function generatePreference(Product $product)
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));

        $client = new PreferenceClient();
        $price = $product->price;
        $preference = $client->create([
            "items" => [
                [
                    "id" => "{{$product->id}}",
                    "title" => "{{$product->name}}",
                    "quantity" => 1,
                    "unit_price" => 10,
                ]
            ],
            "back_urls" => [
                "success" => "http://127.0.0.1:8000/",
                "failure" => "http://127.0.0.1:8000/",
                "pending" => "http://127.0.0.1:8000/",
            ],
            "auto_return" => "approved",
            "notification_url" => "https://61fe-88-0-168-69.ngrok-free.app/webhooks/mercadopago"
        ]);

        return $preference;
    }
}
