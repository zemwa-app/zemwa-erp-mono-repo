<?php

namespace Modules\RestAPI\Http\Requests\Holiday;

use Modules\RestAPI\Http\Requests\BaseRequest;

class IndexRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            //
        ];
    }
}
