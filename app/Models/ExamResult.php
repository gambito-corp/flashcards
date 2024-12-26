<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamResult extends Model
{
    use SoftDeletes;

    protected $table = 'exam_results';

    protected $fillable = [
        'user_id',
        'exam_id',
        'total_score',
    ];

    protected $casts = [
        'total_score' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
