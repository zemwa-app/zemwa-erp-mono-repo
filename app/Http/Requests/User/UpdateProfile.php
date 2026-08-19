<?php

namespace App\Http\Requests\User;

use App\Http\Requests\CoreRequest;

class UpdateProfile extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $setting = companyOrGlobalSetting();
        $rules = [
            'name' => 'required|max:50',
            'password' => 'nullable|min:8|max:50',
            'image' => 'image|max:2048',
            'mobile' => 'nullable|numeric',
            'date_of_birth' => 'nullable|date_format:"' . $setting->date_format . '"|before_or_equal:'.now($setting->timezone)->format($setting->date_format),
            'twitter_id' => 'nullable|unique:user_auths,twitter_id,' . $this->route('profile'),
        ];

        if (user()->email != $this->email) {
            if (user()->is_superadmin) {
                $rules['email'] = [
                    'required',
                    'email:rfc,strict',
                    'unique:users,email,' . user()->id . ',id',
                ];
            } else {
                $rules['email'] = [
                    'required',
                    'email:rfc,strict',
                    'unique:users,email,' . user()->id . ',id,company_id,' . company()->id,
                ];
            }
        }

        return $rules;
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'image.image' => 'Profile picture should be an image',
        ];
    }

}

