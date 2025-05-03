<?php

namespace App\Services\Usuarios;

use App\Mail\QuotaExceededNotification;
use App\Models\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Client\RequestException;

class MBIAService
{
    public function search(array $payload)
    {

        // Verificar si el flag de quota excedido está activo
//        $config = Config::query()->where('tipo', 'services.openai.quota_exceeded')->first();
//
//        if (isset($config) && $config->value === 'true') {
//            $payload['client'] !== 'medisearch';
//        }
        try {
            $payload['include_articles'] = $payload['include_articles'] ? 'true' : 'false';
            $request = Http::asForm()->withHeaders([
                'Authorization' => 'Bearer ' . config('services.ai_api.token'),
            ])->post(config('services.ai_api.base_url'), $payload);
            $data = json_decode($request->body(), true);
            // Detectar error 429 en la estructura real
            if (isset($data['data']['resultados'][0]['respuesta'])) {
                $errorMessage = $data['data']['resultados'][0]['respuesta'];
                if (str_contains($errorMessage, 'Error code: 429') &&
                    str_contains($errorMessage, 'insufficient_quota')) {
                    // 1. Actualizar flag en base de datos
                    Config::query()->updateOrCreate(
                        ['tipo' => 'services.MBAI.openai_quota_exceeded'],
                        ['value' => 'true']
                    );
                    // 2. Registrar en logs
                    Log::critical('OpenAI quota exceeded - Fallback activated', [
                        'error' => $errorMessage,
                        'query' => $payload['query'] ?? ''
                    ]);
                    // 3. Notificar por email (opcional)
                    if (config('services.notifications.admin_email') !== ''){
                        Mail::to(config('services.notifications.admin_email'))
                            ->send(new QuotaExceededNotification($payload));
                    }
                    Mail::to(config('services.notifications.admin2_email'))
                        ->send(new QuotaExceededNotification($payload));

                    if ($payload['client'] !== 'medisearch'){
                        $payload['client'] = 'medisearch';
                        return $this->search($payload);
                    }
                }
            }
            return json_decode($request->body(), true);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            throw $e;
        }
    }
}
