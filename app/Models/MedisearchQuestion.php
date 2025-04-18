<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedisearchQuestion extends Model
{
    protected $table = 'medisearch_questions';

    protected $fillable = [
        'user_id',
        'chat_id',
        'model',
        'query',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chat()
    {
        return $this->belongsTo(MedisearchChat::class, 'chat_id');
    }
}
