<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules;

class DisableAccountRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'profile' => ['required', 'string', 'max:255', 'in:CUSTOMER,CONTRIBUTOR,ADMINISTRATOR'],
            'disable_account_confirmation' => ['required', 'boolean'],
            'password' => ['required', 'string', 'max:255', Rules\Password::defaults()]
        ];
    }
}
