<?php

namespace App\Http\Requests\Deal;

use App\Http\Requests\CoreRequest;

class SendEmailRequest extends CoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'to' => 'required|email',
            'cc' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value === null || trim($value) === '') {
                        return;
                    }

                    foreach (array_filter(array_map('trim', explode(',', $value))) as $email) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $fail(__('validation.email', ['attribute' => __('app.cc')]));
                        }
                    }
                },
            ],
            'subject' => 'required|string|max:500',
            'body' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (trim_editor($value) == '') {
                        $fail(__('validation.required'));
                    }
                },
            ],
            'deal_email_template_id' => 'nullable|integer|exists:deal_email_templates,id',
            'file' => 'nullable|array',
            'file.*' => 'file|max:10240',
        ];
    }
}
