<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class SignUpRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $data = $this->validationData();

        $rules = [
            'email' => ['required', 'string', 'email:strict', 'max:255', Rule::unique('users', 'email')->where(fn(Builder $query) => $query->where('profile', $data['profile'] ?? 'CUSTOMER'))],
            'profile' => ['required', 'string', 'max:255', 'in:CUSTOMER,CONTRIBUTOR,ADMINISTRATOR'],
            'has_default_password' => ['nullable', 'boolean'],
            //'password' => [Rule::requiredIf((isset($data['has_default_password']) && !$data['has_default_password']) || !isset($data['has_default_password'])), 'string', 'max:255', Rules\Password::defaults(), 'confirmed'],
            //'password_confirmation' => [Rule::requiredIf((isset($data['has_default_password']) && !$data['has_default_password']) || !isset($data['has_default_password'])), 'string', 'max:255', Rules\Password::defaults(), 'same:password'],
            'terms_condition' => ['required', 'boolean'],
            'validate_account_url' => [Rule::requiredIf(!empty($data['profile']) && 'CUSTOMER' == $data['profile']), 'url']
        ];

        if ((isset($data['has_default_password']) && !$data['has_default_password']) || !isset($data['has_default_password'])) {
            $rules['password'] = ['required', 'string', 'max:255', Rules\Password::defaults(), 'confirmed'];
            $rules['password_confirmation'] = ['required', 'string', 'max:255', Rules\Password::defaults(), 'same:password'];
        } else {
            $rules['password'] = ['nullable', 'string', 'max:255', Rules\Password::defaults(), 'confirmed'];
            $rules['password_confirmation'] = ['nullable', 'string', 'max:255', Rules\Password::defaults(), 'same:password'];
        }

        return $rules;
    }
}
