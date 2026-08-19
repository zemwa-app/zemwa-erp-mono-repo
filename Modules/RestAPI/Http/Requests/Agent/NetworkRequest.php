<?php

namespace Modules\RestAPI\Http\Requests\Agent;

use Modules\RestAPI\Http\Requests\BaseRequest;

class NetworkRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|string',
            'hour' => 'required|date',
            'total_bytes_sent' => 'required|integer|min:0',
            'total_bytes_received' => 'required|integer|min:0',
            'top_processes' => 'nullable|array',
            'top_processes.*.process' => 'required_with:top_processes|string|max:255',
            'top_processes.*.bytes_sent' => 'required_with:top_processes|integer|min:0',
            'top_processes.*.bytes_received' => 'required_with:top_processes|integer|min:0',
            'cloud_uploads_detected' => 'nullable|array',
            'cloud_uploads_detected.*' => 'string|max:255',
            'vpn_active' => 'nullable|boolean',
            'large_transfer_alert' => 'nullable|boolean',
        ];
    }
}
