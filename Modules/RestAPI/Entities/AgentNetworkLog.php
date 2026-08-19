<?php

namespace Modules\RestAPI\Entities;

use Illuminate\Database\Eloquent\Model;

class AgentNetworkLog extends Model
{
    protected $table = 'agent_network_logs';

    protected $fillable = [
        'company_id',
        'user_id',
        'hour',
        'total_bytes_sent',
        'total_bytes_received',
        'top_processes',
        'cloud_uploads_detected',
        'vpn_active',
        'large_transfer_alert',
    ];

    protected $casts = [
        'hour' => 'datetime',
        'total_bytes_sent' => 'integer',
        'total_bytes_received' => 'integer',
        'top_processes' => 'array',
        'cloud_uploads_detected' => 'array',
        'vpn_active' => 'boolean',
        'large_transfer_alert' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
