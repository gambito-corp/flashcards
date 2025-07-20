<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityIncident extends Model
{
    use HasFactory;

    protected $table = 'security_incidents';

    protected $fillable = [
        'user_id',
        'ip_address',
        'occurred_at',
        'type',
        'severity',
        'url',
        'payload',
        'user_agent',
        'blocked',
        'notes',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'blocked' => 'boolean',
        'payload' => 'array',     // si guardas JSON lo recibes como array
    ];

    /* Relación opcional */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
