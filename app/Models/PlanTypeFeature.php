<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanTypeFeature extends Pivot
{
    use softDeletes;
    protected $table = 'plan_type_feature';

    public $incrementing = true;

    protected $fillable = ['plan_type_id', 'feature_id', 'value'];
}
