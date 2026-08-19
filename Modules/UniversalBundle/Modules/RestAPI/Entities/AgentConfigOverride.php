<?php

namespace Modules\RestAPI\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentConfigOverride extends Model
{
    protected $table = 'agent_config_overrides';

    protected $fillable = [
        'company_id',
        'user_id',
        'screenshot',
        'app_tracking',
        'keyboard',
        'network',
    ];

    protected $casts = [
        'screenshot' => 'array',
        'app_tracking' => 'array',
        'keyboard' => 'array',
        'network' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
