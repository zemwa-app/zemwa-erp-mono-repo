<?php

namespace App\Http\Requests\LeadForm;

use App\Http\Requests\CoreRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends CoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('lead_forms', 'slug')->where('company_id', company()->id),
            ],
            'status' => 'nullable|in:active,inactive',
            'lead_pipeline_id' => 'nullable|integer|exists:lead_pipelines,id',
            'pipeline_stage_id' => 'nullable|integer|exists:pipeline_stages,id',
            'category_id' => 'nullable|integer|exists:lead_category,id',
            'lead_source_id' => 'nullable|integer|exists:lead_sources,id',
        ];
    }
}
