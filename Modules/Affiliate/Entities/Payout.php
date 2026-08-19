<?php

namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Affiliate\Enums\PaymentStatus;
use Modules\Affiliate\Enums\PayoutMethod;

class Payout extends Model
{
    protected $guarded = ['id'];

    protected $table = 'affiliate_payouts';

    protected $casts = [
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'status' => PaymentStatus::class,
        'payment_method' => PayoutMethod::class,
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }
}
