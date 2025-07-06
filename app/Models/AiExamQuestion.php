<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiExamQuestion extends Model
{
    use softDeletes;

    protected $table = 'ai_exam_questions';

    protected $fillable = [
        'user_id',
        'exam_id',
        'question_id',
        'points',
    ];

    protected $casts = [
        'points' => 'float',
    ];

    public function aiExam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function aiQuestion()
    {
        return $this->belongsTo(AiQuestion::class, 'question_id');
    }
}
