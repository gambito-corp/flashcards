<?php

namespace App\Services\Usuarios;

use Illuminate\Support\Facades\Http;

class MBIAService
{
    public function search(array $payload)
    {
        $payload['include_articles'] = $payload['include_articles'] ? 'true' : 'false';
        $request = Http::asForm()->withHeaders([
            'Authorization' => 'Bearer ' . config('services.ai_api.token'), // Pon tu token en config/services.php
        ])->post(config('services.ai_api.base_url'), $payload);
        return json_decode($request->body(), true);
    }
}
