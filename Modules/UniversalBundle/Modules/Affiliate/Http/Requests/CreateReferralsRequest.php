<?php

namespace Modules\Affiliate\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReferralsRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'affiliate_id' => 'required|numeric'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [
            'company_id' => __('affiliate::app.customer'),
        ];
    }

}
