<?php

namespace Modules\Affiliate\Entities;

use App\Models\BaseModel;
use Modules\Affiliate\Enums\YesNo;
use Modules\Affiliate\Enums\PayoutTime;
use Modules\Affiliate\Enums\PayoutType;
use Modules\Affiliate\Enums\CommissionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliateSetting extends BaseModel
{

    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'commission_enabled' => YesNo::class,
        'payout_type' => PayoutType::class,
        'payout_time' => PayoutTime::class,
        'commission_type' => CommissionType::class,
    ];
}
