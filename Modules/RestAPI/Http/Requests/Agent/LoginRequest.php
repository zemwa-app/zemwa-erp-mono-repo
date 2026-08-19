<?php

namespace Modules\RestAPI\Http\Requests\Agent;

use Modules\RestAPI\Http\Requests\BaseRequest;

class LoginRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:1',
        ];
    }
}
