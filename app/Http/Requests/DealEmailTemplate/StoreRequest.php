<?php

namespace App\Http\Requests\DealEmailTemplate;

use App\Http\Requests\CoreRequest;

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
            'subject' => 'required|string|max:500',
            'body' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (trim_editor($value) == '') {
                        $fail(__('validation.required'));
                    }
                },
            ],
        ];
    }
}
