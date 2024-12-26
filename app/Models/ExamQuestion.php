<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamQuestion extends Pivot
{
    use SoftDeletes;

    protected $table = 'exam_question';

    protected $fillable = [
        'exam_id',
        'question_id',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
