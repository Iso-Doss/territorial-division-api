<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class ForgotPasswordRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:strict', 'max:255', Rule::exists('users', 'email')->where(fn(Builder $query) => $query->whereNotNull('activated_at')->whereNotNull('verified_at')->whereNull('deleted_at'))],
            'profile' => ['required', 'string', 'max:255', 'in:CUSTOMER,CONTRIBUTOR,ADMINISTRATOR'],
            'reset_password_url' => ['required', 'url']
        ];
    }
}
