<?php

namespace App\Http\Resources\IA\Medisearch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BotResponseResource extends JsonResource
{
    public $tipo = 'bot';
    public function toArray($request)
    {
        return [
            'from' => 'bot',
            'text' => $this->resource,
        ];
    }
}
