<?php

namespace Modules\RestAPI\Http\Requests\Agent;

use Modules\RestAPI\Http\Requests\BaseRequest;

class ActivityWindowRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*.employee_id' => 'nullable|string',
            '*.window_start' => 'required|date',
            '*.window_end' => 'required|date|after:*.window_start',
            '*.keystrokes' => 'required|integer|min:0',
            '*.mouse_clicks' => 'required|integer|min:0',
            '*.mouse_distance' => 'nullable|integer|min:0',
            '*.scroll_events' => 'nullable|integer|min:0',
            '*.activity_pct' => 'required|numeric|min:0|max:100',
            '*.is_idle' => 'required|boolean',
        ];
    }
}
