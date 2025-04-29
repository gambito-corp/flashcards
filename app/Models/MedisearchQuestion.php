<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedisearchQuestion extends Model
{
    use SoftDeletes;
    protected $table = 'medisearch_questions';

    protected $fillable = [
        'user_id',
        'chat_id',
        'model',
        'query',
        'response',
        'feedback',
        'points',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    public function toConversationEntry(): array
    {
        return [
            'user' => $this->query,
            'system' => $this->response['data']['resultados'][0]['respuesta'] ?? '' // Asume que response es un array con clave 'response'
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chat()
    {
        return $this->belongsTo(MedisearchChat::class, 'chat_id');
    }
}
