<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiOption extends Model
{
    use softDeletes;

    protected $fillable = [
        'question_id',
        'content',
        'is_correct',
        'points',
    ];
    protected $casts = [
        'is_correct' => 'boolean',
        'points' => 'float',
    ];

    public function question()
    {
        return $this->belongsTo(AiQuestion::class, 'question_id');
    }
}
