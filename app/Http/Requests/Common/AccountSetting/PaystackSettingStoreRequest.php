<?php

namespace App\Http\Requests\Common\AccountSetting;

use App\Http\Requests\BaseFormRequest;

class PaystackSettingStoreRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'paystack_public_key'      => 'required|string|max:100',
            'paystack_secret_key'      => 'required|string|max:100'
        ];
    }
}
