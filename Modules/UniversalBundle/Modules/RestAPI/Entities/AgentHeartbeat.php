<?php

namespace Modules\RestAPI\Entities;

use Illuminate\Database\Eloquent\Model;

class AgentHeartbeat extends Model
{
    protected $table = 'agent_heartbeats';

    protected $fillable = [
        'company_id',
        'user_id',
        'agent_version',
        'os',
        'os_version',
        'hostname',
        'is_idle',
        'is_paused',
        'active_app',
        'pending_sync_count',
        'event_timestamp',
    ];

    protected $casts = [
        'is_idle' => 'boolean',
        'is_paused' => 'boolean',
        'event_timestamp' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
