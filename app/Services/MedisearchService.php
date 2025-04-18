<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MedisearchService
{
    public function search(mixed $query, mixed $investigacionProfunda)
    {
        $endpoint = 'https://appbanqueo.medbystudents.com/api/python/search';
        $response = Http::get($endpoint, [
            'q' => $query,
        ]);
        $data = $response->json();
        if (isset($data['data']['resultados'])) {
            foreach ($data['data']['resultados'] as $item) {
                if ($item['tipo'] === 'articles') {
                    $this->messages[] = [
                        'from' => 'articles',
                        'data' => $item['articulos'],
                    ];
                } elseif ($item['tipo'] === 'llm_response') {
                    $this->messages[] = [
                        'from' => 'bot',
                        'text' => $item['respuesta'],
                    ];
                }
            }
        }
        return $data;
    }
}
