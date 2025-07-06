<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiQuestion extends Model
{

    use softDeletes;

    protected $fillable = [
        'user_id',
        'exam_id',
        'type',
        'content',
        'explanation',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function options()
    {
        return $this->hasMany(AiOption::class, 'question_id')->inRandomOrder();
    }

    public function optionsCorrectas()
    {
        return $this->hasMany(AiOption::class, 'question_id')->where('is_correct', true);
    }

}
