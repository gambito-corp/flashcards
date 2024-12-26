<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionEdge extends Model
{
    use SoftDeletes;

    protected $table = 'question_edges';

    protected $fillable = [
        'from_question_id',
        'option_id',
        'to_question_id',
        // 'is_default', 'condition', etc. si los manejas
    ];

    /**
     * La pregunta de origen (from).
     */
    public function fromQuestion()
    {
        return $this->belongsTo(Question::class, 'from_question_id');
    }

    /**
     * La opción que dispara la transición (puede ser null).
     */
    public function option()
    {
        return $this->belongsTo(Option::class, 'option_id');
    }

    /**
     * La pregunta de destino (to).
     */
    public function toQuestion()
    {
        return $this->belongsTo(Question::class, 'to_question_id');
    }
}
