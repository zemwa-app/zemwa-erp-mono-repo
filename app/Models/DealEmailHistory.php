<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DealEmailHistory extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
    ];

    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DealEmailTemplate::class, 'deal_email_template_id');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DealEmailAttachment::class, 'deal_email_history_id');
    }
}
