<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FcCardsGroupCard extends Pivot
{
    protected $table = 'fc_cards_group_cards';

    protected $fillable = [
        'fc_group_card_id',
        'fc_card_id',
    ];
}
