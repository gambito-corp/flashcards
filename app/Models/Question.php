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
        'explanation',
        'fail_weight'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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

    public function universidades()
    {
        return $this->belongsToMany(Universidad::class, 'question_universidad')
            ->using(QuestionUniversidad::class) // Necesario para tablas con id autoincremental
            ->withPivot('id') // Incluir el id del pivot
            ->select('universidades.id as universidad_id', 'universidades.name');
    }

    public function options()
    {
        return $this->hasMany(Option::class)->inRandomOrder();
    }

    public function optionsCorrectas()
    {
        return $this->hasMany(Option::class, 'question_id')->where('is_correct', true);
    }

    public function examUserAnswers()
    {
        return $this->hasMany(ExamUserAnswer::class);
    }
}
