<?php

namespace Modules\Affiliate\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Affiliate\Entities\Affiliate;
use Modules\Affiliate\Entities\AffiliateSetting;

class StorePayout extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $min_payout = AffiliateSetting::first()->minimum_payout ?? 1;
        $affiliateBalance = Affiliate::find(request('affiliate_id'))?->balance;

        return [
            'affiliate_id' => 'required',
            'amount' => request('affiliate_id') == null ? 'required' : 'required|numeric|min:' . $min_payout . '|max:' . $affiliateBalance,
            'payment_method' => 'required',
            'other_payment_method' => 'required_if:payment_method,other',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function attributes()
    {
        return [
            'affiliate_id' => __('affiliate::app.affiliateName'),
        ];
    }

}
