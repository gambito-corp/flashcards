<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedisearchChat extends Model
{
    protected $fillable = [
        'user_id',
        'title',
    ];

    // Un chat tiene muchas preguntas/respuestas
    public function questions()
    {
        return $this->hasMany(MedisearchQuestion::class, 'chat_id');
    }

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
