<?php

namespace Modules\RestAPI\Entities;

use Illuminate\Database\Eloquent\Model;

class AgentEvent extends Model
{
    protected $table = 'agent_events';

    protected $fillable = [
        'company_id',
        'user_id',
        'event_type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
