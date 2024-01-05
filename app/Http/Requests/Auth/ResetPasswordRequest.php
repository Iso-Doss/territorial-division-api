<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class ResetPasswordRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $data = $this->validationData();

        return [
            'email' => ['required', 'string', 'email:strict', 'max:255', Rule::exists('users', 'email')->where(fn(Builder $query) => $query->where('profile', $data['profile'] ?? 'CUSTOMER')->whereNotNull('activated_at')->whereNotNull('verified_at')->whereNull('deleted_at')), Rule::exists('password_reset_tokens', 'email')->where(fn(Builder $query) => $query->where('profile', $data['profile'] ?? 'CUSTOMER')->where('type', 'FORGOT-PASSWORD')->where('token', $data['token']))],
            'profile' => ['required', 'string', 'max:255', 'in:CUSTOMER,CONTRIBUTOR,ADMINISTRATOR'],
            'token' => ['required', 'string', 'max:255', Rule::exists('password_reset_tokens', 'token')->where(fn(Builder $query) => $query->where('profile', $data['profile'] ?? 'CUSTOMER')->where('type', 'FORGOT-PASSWORD')->where('email', $data['email']))],
            'password' => ['required', 'string', 'max:255', Rules\Password::defaults(), 'confirmed'],
            'password_confirmation' => ['required', 'string', 'max:255', Rules\Password::defaults(), 'same:password']
        ];
    }
}
