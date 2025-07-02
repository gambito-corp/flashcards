<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class QuestionTipo extends Pivot
{
    protected $table = 'question_tipo';
    protected $fillable = [
        'question_id',
        'tipo_id',
    ];

    public function tipo()
    {
        return $this->belongsTo(Tipo::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
