<?php

namespace App\Http\Resources\IA\OpenAI;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public $tipo = 'articles';
    public function toArray($request)
    {
        dd($this->resource);
        return [
            'from' => 'articles',
            'data' => $this->resource,
        ];
    }
}
