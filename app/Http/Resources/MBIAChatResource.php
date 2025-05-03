<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MBIAChatResource extends JsonResource
{
    public function toArray($request)
    {
        $messages = [];

        if (isset($this['data']['resultados'])) {
            foreach ($this['data']['resultados'] as $item) {
                if ($item['tipo'] === 'articles') {
                    $messages[] = [
                        'from' => 'articles',
                        'data' => $item['articulos'],
                    ];
                } elseif ($item['tipo'] === 'llm_response') {
                    $messages[] = [
                        'from' => 'bot',
                        'text' => $item['respuesta'],
                    ];
                }
            }
        }

        return [
            'query' => $this['data']['query'] ?? null,
            'messages' => $messages,
        ];
    }
}
