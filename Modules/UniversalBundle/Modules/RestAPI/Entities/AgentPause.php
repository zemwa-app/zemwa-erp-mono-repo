<?php

namespace Modules\RestAPI\Entities;

use Illuminate\Database\Eloquent\Model;

class AgentPause extends Model
{
    protected $table = 'agent_pauses';

    protected $fillable = [
        'company_id',
        'user_id',
        'reason',
        'duration_minutes',
        'started_at',
        'ends_at',
        'resumed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'resumed_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
