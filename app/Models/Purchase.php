<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;
    // Especificamos la tabla (opcional)
    protected $table = 'purchases';

    // Campos rellenables.
    protected $fillable = [
        'user_id',
        'product_id',
        'purchased_at',
        'preaproval_id'
    ];

    /**
     * Relación: Una compra pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Una compra pertenece a un producto.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
