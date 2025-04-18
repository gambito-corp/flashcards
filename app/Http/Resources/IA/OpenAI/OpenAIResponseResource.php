<?php

namespace App\Http\Resources\IA\OpenAI;

use App\Http\Resources\IA\OpenAI\ArticleResource;
use App\Http\Resources\IA\OpenAI\BotResponseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpenAIResponseResource extends JsonResource
{
    public function toArray($request)
    {
        $messages = [];
        if (isset($this->resource)) {
            foreach ($this->resource as $key => $item) {
                if ($key === 'urls') {
                    $messages[] = new ArticleResource($item);
                } elseif ($key === 'clean_text') {
                    $messages[] = new BotResponseResource($item);
                }
            }
        }

        return $messages;
    }
}
