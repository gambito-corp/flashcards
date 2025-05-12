<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['code', 'name', 'description'];

    public function planTypes()
    {
        return $this->belongsToMany(PlanType::class, 'plan_type_feature')
            ->withPivot('value')
            ->withTimestamps();
    }
}
