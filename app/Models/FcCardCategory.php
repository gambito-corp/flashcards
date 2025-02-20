<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FcCardCategory extends Pivot
{
    protected $table = 'fc_card_category';

    protected $fillable = [
        'fc_card_id',
        'fc_category_id',
    ];
}
