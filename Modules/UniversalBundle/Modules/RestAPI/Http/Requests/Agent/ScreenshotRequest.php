<?php

namespace Modules\RestAPI\Http\Requests\Agent;

use Modules\RestAPI\Http\Requests\BaseRequest;

class ScreenshotRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|max:10240|mimes:jpeg,jpg,png,bmp,gif,webp',
            'metadata' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'A screenshot file is required.',
            'file.file' => 'The file must be a valid uploaded file.',
            'file.max' => 'The screenshot must not exceed 10MB.',
            'file.mimes' => 'The file must be a jpeg, png, bmp, gif, or webp image.',
            'metadata.required' => 'Screenshot metadata is required.',
        ];
    }
}
