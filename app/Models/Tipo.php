<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipo extends Model
{
    use HasFactory;
    protected $fillable = ['category_id', 'name'];

    /**
     * Relación: Un Tipo pertenece a una Categoría.
     */
    public function categoria()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    // Un tipo pertenece a una categoría
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_tipo')
            ->withTimestamps();
    }

    // Nueva relación many-to-many con universidades
    public function universidades()
    {
        return $this->belongsToMany(Universidad::class, 'tipo_universidad')->withTimestamps();
    }
}
