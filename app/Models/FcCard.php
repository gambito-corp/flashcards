<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcCard extends Model
{
    protected $table = 'fc_cards';

    protected $fillable = [
        'user_id',
        'pregunta',
        'url',
        'imagen',
        'respuesta',
        'url_respuesta',
        'imagen_respuesta'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(
            FcCategory::class,
            'fc_card_category',
            'fc_card_id',
            'fc_category_id'
        )
            ->using(FcCardCategory::class)
            ->withTimestamps();
    }
}
