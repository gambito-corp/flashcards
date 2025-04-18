<?php

namespace App\Http\Resources\IA;

use App\Http\Resources\IA\Medisearch\MedisearchResponseResource;
use App\Http\Resources\IA\OpenAI\OpenAIResponseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AIResponseResourceFactory extends JsonResource
{
    public function getResource(string $model, array $data)
    {
        return match ($model) {
            'medisearch' => new MedisearchResponseResource(['data' => $data]),
            'MBIA' => new OpenAIResponseResource($data),
            default => new MedisearchResponseResource(['data' => $data]),
        };
    }
}
