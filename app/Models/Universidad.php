<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Universidad extends Model
{
    use HasFactory;

    protected $table = 'universidades';

    protected $fillable = ['tipo_id', 'name'];

    // Relación many-to-many con Tipo
    public function tipos()
    {
        return $this->belongsToMany(Tipo::class, 'tipo_universidad')->withTimestamps();
    }

    /**
     * Relación: Una Universidad puede tener muchas preguntas a través de la tabla pivote question_universidad.
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_universidad')
            ->withTimestamps();
    }
}
