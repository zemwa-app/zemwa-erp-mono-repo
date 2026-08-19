<?php

namespace Modules\Monitor\Entities;

use Illuminate\Database\Eloquent\Model;

class AgentSession extends Model
{
    protected $table = 'agent_sessions';

    protected $fillable = [
        'company_id',
        'user_id',
        'is_online',
        'last_seen_at',
        'active_app',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
