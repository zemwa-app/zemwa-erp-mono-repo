<?php

namespace Modules\RestAPI\Entities;

use Illuminate\Database\Eloquent\Model;

class AgentActivityWindow extends Model
{
    protected $table = 'agent_activity_windows';

    protected $fillable = [
        'company_id',
        'user_id',
        'window_start',
        'window_end',
        'keystrokes',
        'mouse_clicks',
        'mouse_distance',
        'scroll_events',
        'activity_pct',
        'is_idle',
    ];

    protected $casts = [
        'window_start' => 'datetime',
        'window_end' => 'datetime',
        'keystrokes' => 'integer',
        'mouse_clicks' => 'integer',
        'mouse_distance' => 'integer',
        'scroll_events' => 'integer',
        'activity_pct' => 'float',
        'is_idle' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
