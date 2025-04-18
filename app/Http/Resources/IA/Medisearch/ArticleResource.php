<?php

namespace App\Http\Resources\IA\Medisearch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public $tipo = 'articles';
    public function toArray($request)
    {
        return [
            'from' => 'articles',
            'data' => $this->resource,
        ];
    }
}
