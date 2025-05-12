<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanType extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['name', 'description'];

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'plan_type_feature')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    public function getFeatureValue($code)
    {
        $feature = $this->features()->where('code', $code)->first();
        return $feature ? $feature->pivot->value : null;
    }
}
