<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcCategory extends Model
{
    protected $table = 'fc_categories';

    protected $fillable = [
        'user_id',
        'nombre'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cards()
    {
        return $this->belongsToMany(
            FcCard::class,
            'fc_card_category',
            'fc_category_id',
            'fc_card_id'
        )
            ->using(FcCardCategory::class)
            ->withTimestamps();
    }
}
