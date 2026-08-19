<?php

namespace Modules\RestAPI\Http\Requests\RestAPISetting;

use Modules\RestAPI\Http\Requests\BaseRequest;

class SendPushRequest extends BaseRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'platform' => 'required|in:ios,android',
            'device_id' => 'required|string',
        ];
    }

}
