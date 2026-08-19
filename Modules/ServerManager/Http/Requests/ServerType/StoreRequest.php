<?php

namespace Modules\ServerManager\Http\Requests\ServerType;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:server_types,name,NULL,id,company_id,' . company()->id,
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'slug' => 'nullable|string|max:255|unique:server_types,slug,NULL,id,company_id,' . company()->id,
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return user()->permission('manage_servertype') === 'all';
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'The server type name is required.',
            'name.unique' => 'This server type name already exists in your company.',
            'status.in' => 'The status must be either active or inactive.',
        ];
    }
}

