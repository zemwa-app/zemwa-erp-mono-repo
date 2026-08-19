<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadForm extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(LeadCustomForm::class, 'lead_form_id')->orderBy('field_order');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(LeadPipeline::class, 'lead_pipeline_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LeadCategory::class, 'category_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'lead_form_id');
    }

    public static function defaultForCompany(?int $companyId = null): ?self
    {
        $companyId = $companyId ?? company()->id;

        return static::where('company_id', $companyId)->where('is_default', 1)->first();
    }

    public function getPublicUrlAttribute(): string
    {
        return route('front.lead_form', [$this->company->hash, $this->slug]);
    }
}
