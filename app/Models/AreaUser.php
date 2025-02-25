<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model;

class AreaUser extends Pivot
{
    protected $table = 'area_user';

    protected $fillable = [
        'user_id',
        'area_id',
    ];

}
