<?php

namespace Modules\Affiliate\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Affiliate\Enums\Status;

class Affiliate extends BaseModel
{

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => Status::class,
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('affiliates.status', 'active');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->where('status', 'active');
    }

    public function referral()
    {
        return $this->hasOne(Referral::class);
    }
}
