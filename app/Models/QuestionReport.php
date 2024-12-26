<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionReport extends Model
{
    use SoftDeletes;

    protected $table = 'question_reports';

    protected $fillable = [
        'user_id',
        'exam_id',
        'question_id',
        'option_id',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
        // Si quisieras forzar un enum, habría que manejarlo en validaciones.
    ];

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
