<?php

namespace App\Http\Requests\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class SignInRequest extends BaseRequest
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
            'email' => ['required', 'string', 'email:strict', 'max:255', Rule::exists('users', 'email')->where(fn(Builder $query) => $query->where('profile', $data['profile'] ?? 'CUSTOMER')->whereNotNull('activated_at')->whereNotNull('verified_at')->whereNull('deleted_at'))],
            'profile' => ['required', 'string', 'max:255', 'in:CUSTOMER,CONTRIBUTOR,ADMINISTRATOR'],
            'password' => ['required', 'string', 'max:255', Rules\Password::defaults()]
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $guard = Controller::getGuard($this->validated('profile'));

        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password', 'profile');

        $user = User::whereEmail($this->validated('email'))
            ->whereProfile($this->validated('profile'))
            ->whereNotNull('activated_at')
            ->whereNotNull('verified_at')
            ->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey());

            if ($this->isApiRequest()) {
                $response = Controller::sendResponse(false, "Identifiant incorrect ou compte non actif.", $this->validator->errors(), [], $this->all(), 404);
                throw (new ValidationException($this->validator, $response))->status(400);
            } else {
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
        }

        if (!Auth::guard($guard)->attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            if ($this->isApiRequest()) {
                $response = Controller::sendResponse(false, "Un ou plusieurs champs sont incorrects.", $this->validator->errors(), [], $this->all(), 404);
                throw (new ValidationException($this->validator, $response))->status(400);
            } else {
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }

        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')) . '|' . Str::lower($this->input('profile')) . '|' . $this->ip());
    }
}
