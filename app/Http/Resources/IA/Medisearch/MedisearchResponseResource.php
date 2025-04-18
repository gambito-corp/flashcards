<?php

namespace App\Http\Resources\IA\Medisearch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedisearchResponseResource extends JsonResource
{
    public function toArray($request)
    {
        $messages = [];
        if (isset($this->resource['data']['data']['resultados'])) {
            foreach ($this->resource['data']['data']['resultados'] as $item) {
                if ($item['tipo'] === 'articles') {
                    $messages[] = new ArticleResource($item['articulos']);
                } elseif ($item['tipo'] === 'llm_response') {
                    $messages[] = new BotResponseResource($item['respuesta']);
                }
            }
        }
        return $messages;
    }
}
