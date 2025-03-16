<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'content',
        'question_type',
        'range_min',
        'range_max',
        'media_type',
        'media_url',
        'media_iframe',
        'approved',
        'explanation'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Una pregunta puede pertenecer a múltiples tipos.
     */
    public function tipos()
    {
        return $this->belongsToMany(Tipo::class, 'question_tipo')
            ->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_question')
            ->withTimestamps();
    }
    /**
     * Una pregunta puede pertenecer a múltiples universidades.
     */
    public function universidades()
    {
        return $this->belongsToMany(Universidad::class, 'question_universidad');
    }

    public function options()
    {
        return $this->hasMany(Option::class)->inRandomOrder();
    }
}
