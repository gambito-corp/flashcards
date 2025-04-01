<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Option extends Model
{
    use SoftDeletes;

    protected $table = 'options';

    protected $fillable = [
        'question_id',
        'content',
        'is_correct',
        'points',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points'     => 'float',
    ];

    /**
     * La opción pertenece a una pregunta.
     */
    public function question()
    {
        return $this->belongsTo(Question::class)->withTrashed();
    }
}
