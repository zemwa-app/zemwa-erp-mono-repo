<?php

namespace Modules\RestAPI\Http\Requests\Auth;

use Modules\RestAPI\Http\Requests\BaseRequest;

class TwoFactorAuthRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'method' => 'required|in:email,google_authenticator',
            'code' => 'nullable|string|digits:6',
            'recovery_code' => 'nullable|string',
            'two_fa_verify_via' => 'nullable|string'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'code.digits' => 'The code must be exactly 6 digits.',
            'method.in' => 'The method must be either email or google_authenticator.',
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
