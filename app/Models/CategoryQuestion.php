<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryQuestion extends Pivot
{
    use SoftDeletes;

    protected $table = 'category_question';

    protected $fillable = [
        'category_id',
        'question_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
