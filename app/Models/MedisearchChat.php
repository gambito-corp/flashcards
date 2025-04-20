<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedisearchChat extends Model
{
    use SoftDeletes;
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
