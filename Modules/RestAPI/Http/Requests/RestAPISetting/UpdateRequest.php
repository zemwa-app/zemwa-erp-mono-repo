<?php

namespace Modules\RestAPI\Http\Requests\RestAPISetting;

use Modules\RestAPI\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'firebase_enabled' => 'nullable|boolean',
            'fcm_service_account_file' => 'nullable|file|mimes:json|max:2048',
        ];
    }

}
