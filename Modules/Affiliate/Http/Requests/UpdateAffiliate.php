<?php

namespace Modules\Affiliate\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAffiliate extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'referral_code' => 'required|unique:affiliates,referral_code,' . $this->route('affiliates_dashboard') . '|min:8|max:16|alpha_dash',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
