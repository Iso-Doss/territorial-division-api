<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UpdateProfileRequest extends BaseRequest
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
        $data = $this->all();
        $rules = [
            'profile' => ['required', 'string', 'max:255', 'in:CUSTOMER,CONTRIBUTOR,ADMINISTRATOR'],
            'user_type' => ['required', 'string', 'max:255', 'in:PHYSICAL-PERSON,CORPORATION'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,gif,pdf'],
            'delete_image' => ['nullable', 'boolean'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'ifu' => ['nullable', 'numeric', 'max:255'],
            'user_name' => ['nullable', 'string', 'max:255', Rule::unique('users', 'user_name')->where(fn(Builder $query) => $query->where('profile', $this->input('profile') ?? 'CUSTOMER')->where('id', '<>', Auth::user()->id))],
            //'email' => ['required', 'email:strict', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'numeric', Rule::unique('users', 'phone_number')->where(fn(Builder $query) => $query->where('profile', $this->input('profile') ?? 'CUSTOMER')->where('id', '<>', Auth::user()->id))],
            'is_whatsapp_phone_number' => ['nullable', 'boolean'],
            'whatsapp_phone_number' => ['nullable', 'numeric', Rule::unique('users', 'whatsapp_phone_number')->where(fn(Builder $query) => $query->where('profile', $this->input('profile') ?? 'CUSTOMER')->where('id', '<>', Auth::user()->id))],
            'city' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:255', 'in:FEMALE,MALE,OTHER'],
            'address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255', 'url'],
            'country_id' => ['nullable', 'numeric', Rule::exists('countries', 'id')->where(fn(Builder $query) => $query->whereNotNull('activated_at')->whereNull('deleted_at'))],
            'password' => ['required', 'string', 'max:255', Rules\Password::defaults()]
        ];

        if (!empty($data['user_type']) && 'PHYSICAL-PERSON' == $data['user_type']) {
            $rules['first_name'] = ['required', 'string', 'max:255'];
            $rules['last_name'] = ['required', 'string', 'max:255'];
            $rules['name'] = ['nullable', 'string', 'max:255'];
            $rules['ifu'] = ['nullable', 'numeric', 'max:255'];
        } else if (!empty($data['user_type']) && 'CORPORATION' == $data['user_type']) {
            $rules['first_name'] = ['nullable', 'string', 'max:255'];
            $rules['last_name'] = ['nullable', 'string', 'max:255'];
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['ifu'] = ['required', 'numeric', 'max:255'];
        }

        return $rules;
    }
}
