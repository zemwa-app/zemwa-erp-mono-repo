<?php

namespace Modules\RestAPI\Http\Requests\Agent;

use Modules\RestAPI\Http\Requests\BaseRequest;

class ActivityRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*.employee_id' => 'nullable|string',
            '*.app_name' => 'required|string|max:255',
            '*.process_name' => 'nullable|string|max:255',
            '*.window_title' => 'nullable|string|max:500',
            '*.url' => 'nullable|string|max:2048',
            '*.category' => 'nullable|string|in:productive,unproductive,neutral',
            '*.started_at' => 'required|date',
            '*.ended_at' => 'nullable|date',
            '*.duration_seconds' => 'required|integer|min:0',
        ];
    }
}
