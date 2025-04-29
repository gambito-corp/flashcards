<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Config extends Model
{
    use softDeletes;
    protected $table = 'config';
    protected $fillable = [
        'tipo',
        'value',
        'status'
    ];
}
