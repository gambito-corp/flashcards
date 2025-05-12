<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = [
        'plan_type_id',
        'plan_period_id',
        'price',
        'stripe_price_id',
        'active'
    ];

    public function planType()
    {
        return $this->belongsTo(PlanType::class);
    }

    public function planPeriod()
    {
        return $this->belongsTo(PlanPeriod::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFeatureValue($code)
    {
        return $this->planType->getFeatureValue($code);
    }
}
