<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // Especificamos la tabla (opcional si el nombre sigue la convención plural)
    protected $table = 'products';

    // Campos que se pueden asignar masivamente.
    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'description',
    ];

    /**
     * Relación: Un producto puede ser comprado en muchas ocasiones.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
