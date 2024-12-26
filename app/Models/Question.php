<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $table = 'questions';

    protected $fillable = [
        'content',
        'question_type',
        'is_dag',
        'range_min',
        'range_max',
        'media_type',
        'media_url',
    ];

    protected $casts = [
        'is_dag' => 'boolean',
    ];

    /**
     * Muchas preguntas pueden ir a muchos exams (pivot exam_question).
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_question')
            ->using(ExamQuestion::class)
            ->withTimestamps();
    }

    /**
     * Muchas preguntas pueden tener muchas categorías (pivot category_question).
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_question')
            ->using(CategoryQuestion::class)
            ->withTimestamps();
    }

    /**
     * Una pregunta tiene varias opciones (relación 1..N).
     */
    public function options()
    {
        return $this->hasMany(Option::class);
    }

    /**
     * Edges de este question como "origen" en un DAG.
     */
    public function edgesFrom()
    {
        return $this->hasMany(QuestionEdge::class, 'from_question_id');
    }

    /**
     * (Opcional) Si quieres también ver edges entrantes:
     */
    public function edgesTo()
    {
        return $this->hasMany(QuestionEdge::class, 'to_question_id');
    }
}
