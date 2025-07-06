<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $table = 'exams';

    protected $fillable = [
        'title',
        'description',
        'time_limit'
    ];

    /**
     * Relación muchos a muchos con teams (vía exam_team).
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'exam_team')
            ->using(ExamTeam::class)
            ->withTimestamps();
    }

    /**
     * Relación muchos a muchos con questions (vía exam_question).
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_question')
            ->using(ExamQuestion::class)
            ->withTimestamps();
    }

    /**
     * Resultados de usuarios en este examen.
     */
    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    // SECCIÓN AI

    public function aiQuestions()
    {
        return $this->hasMany(AiQuestion::class, 'exam_id');
    }
}
