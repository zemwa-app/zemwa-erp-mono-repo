<?php

namespace Modules\Affiliate\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSettingsRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'commission_enabled' => 'required|string|in:yes,no',
            'payout_type' => 'required|string|in:on signup,after signup',
            'payout_time' => 'required_if:payout_type,after signup',
            'commission_type' => 'required|string|in:fixed,percent',
            'commission_cap' => 'required|numeric',
            'minimum_payout' => 'required|numeric'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

}
