<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanPeriod extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['name', 'duration_months'];

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }
}
