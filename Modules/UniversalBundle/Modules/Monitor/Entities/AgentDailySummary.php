<?php

namespace Modules\Monitor\Entities;

use Illuminate\Database\Eloquent\Model;

class AgentDailySummary extends Model
{
    protected $table = 'agent_daily_summaries';

    protected $fillable = [
        'company_id',
        'user_id',
        'date',
        'avg_activity_pct',
        'active_seconds',
        'idle_seconds',
    ];

    protected $casts = [
        'date' => 'date',
        'avg_activity_pct' => 'float',
        'active_seconds' => 'integer',
        'idle_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
