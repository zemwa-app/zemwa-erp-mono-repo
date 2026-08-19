<?php

namespace Modules\RestAPI\Entities;

use Illuminate\Database\Eloquent\Model;

class AgentProductivityCategory extends Model
{
    protected $table = 'agent_productivity_categories';

    protected $fillable = [
        'company_id',
        'pattern',
        'category',
        'note',
    ];
}
