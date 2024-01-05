<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class ValidateAccountRequest extends BaseRequest
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
        $data = $this->validationData();

        return [
            'email' => ['required', 'string', 'email:strict', 'max:255', Rule::exists('users', 'email')->where(fn(Builder $query) => $query->where('profile', $data['profile'] ?? 'CUSTOMER')->whereNull('activated_at')->whereNull('verified_at')->whereNull('deleted_at')), Rule::exists('password_reset_tokens', 'email')->where(fn(Builder $query) => $query->where('profile', $data['profile'] ?? 'CUSTOMER')->where('type', 'VALIDATE-ACCOUNT')->where('token', $data['token']))],
            'profile' => ['required', 'string', 'max:255', 'in:CUSTOMER'],
            'token' => ['required', 'string', 'max:255', Rule::exists('password_reset_tokens', 'token')->where(fn(Builder $query) => $query->where('profile', $data['profile'] ?? 'CUSTOMER')->where('type', 'VALIDATE-ACCOUNT')->where('email', $data['email']))]
        ];
    }

    /**
     * Prepare for validation.
     *
     * @return ValidateAccountRequest The validate account request.
     */
    protected function prepareForValidation(): ValidateAccountRequest
    {
        return $this->merge([
            'email' => is_null($this->input('email')) ? $this->route('email') : $this->input('email'),
            'profile' => is_null($this->input('profile')) ? $this->route('profile') : $this->input('profile'),
            'token' => is_null($this->input('token')) ? $this->route('token') : $this->input('token'),
            'type' => is_null($this->input('type')) ? $this->route('type') : $this->input('type')
        ]);
    }
}
