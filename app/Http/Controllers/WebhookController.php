<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        \Log::info('Webhook MercadoPago');
    }
}
