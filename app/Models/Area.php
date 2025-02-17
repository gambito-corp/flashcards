<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes;

    protected $table = 'areas';

    protected $fillable = [
        'name',
        'description',
    ];

    // Un área pertenece a un team (carrera)
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Una area tiene muchas categorías.
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}
