<?php

namespace App\Http\Requests\Common\AccountSetting;

use App\Http\Requests\BaseFormRequest;

class GoogleMeetSettingStoreRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'google_meet_client_id'      => 'required|string|max:255',
            'google_meet_client_secret'  => 'required|string|max:255',
            'active_conference'          => 'sometimes|string|in:zoom,google_meet'
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'google_meet_client_id.required'     => __('settings.google_meet_client_id_required'),
            'google_meet_client_secret.required' => __('settings.google_meet_client_secret_required'),
        ];
    }
}
