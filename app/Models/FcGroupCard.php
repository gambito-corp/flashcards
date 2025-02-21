<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcGroupCard extends Model
{

    protected $table = 'fc_group_cards';

    protected $fillable = [
        'user_id',
        'correct',
        'incorrect',
        'total',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cards()
    {
        return $this->belongsToMany(
            FcCard::class,
            'fc_cards_group_cards',
            'fc_group_card_id',
            'fc_card_id'
        )->withTimestamps();
    }
}
