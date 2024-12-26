<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamUser extends Pivot
{
    use SoftDeletes;

    protected $table = 'team_user';

    protected $fillable = [
        'team_id',
        'user_id',
        'role',
    ];

    /**
     * Relación con Team (belongsTo).
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Relación con User (belongsTo).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
