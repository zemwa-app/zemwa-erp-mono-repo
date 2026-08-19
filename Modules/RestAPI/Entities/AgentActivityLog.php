<?php

namespace Modules\RestAPI\Entities;

use Illuminate\Database\Eloquent\Model;

class AgentActivityLog extends Model
{
    protected $table = 'agent_activity_logs';

    protected $fillable = [
        'company_id',
        'user_id',
        'app_name',
        'process_name',
        'window_title',
        'url',
        'category',
        'subcategory',
        'classified_at',
        'started_at',
        'ended_at',
        'duration_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'classified_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
