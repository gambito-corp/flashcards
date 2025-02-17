<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'area_id',
        'name',
        'description',
    ];

    /**
     * Relación con area (belongsTo).
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Muchas categorías pueden tener muchas preguntas (pivot category_question).
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'category_question')
            ->using(CategoryQuestion::class)
            ->withTimestamps();
    }
    public function tipos()
    {
        return $this->hasMany(Tipo::class);
    }
}
