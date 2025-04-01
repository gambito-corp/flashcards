<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class QuestionUniversidad extends Pivot
{
    protected $table = 'question_universidad';

    protected $fillable = [
        'question_id',
        'universidad_id',
    ];

    public function universidad()
    {
        return $this->belongsTo(Universidad::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
