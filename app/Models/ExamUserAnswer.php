<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamUserAnswer extends Model
{
    use SoftDeletes;

    protected $table = 'exam_user_answers';

    protected $fillable = [
        'user_id',
        'exam_id',
        'question_id',
        'option_id',
        'is_correct',
        'fail_weight',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'fail_weight' => 'float',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
