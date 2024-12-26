<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamTeam extends Pivot
{
    use SoftDeletes;

    protected $table = 'exam_team';

    protected $fillable = [
        'team_id',
        'exam_id',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
