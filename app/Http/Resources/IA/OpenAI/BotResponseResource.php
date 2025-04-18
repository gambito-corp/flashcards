<?php

namespace App\Http\Resources\IA\OpenAI;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BotResponseResource extends JsonResource
{
    public $tipo = 'bot';

    public function toArray($request)
    {
        dd($this->resource);
        return [
            'from' => 'bot',
            'text' => $this->resource,
        ];
    }
}
