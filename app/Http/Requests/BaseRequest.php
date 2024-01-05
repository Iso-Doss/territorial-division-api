<?php

namespace App\Http\Requests;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BaseRequest extends FormRequest
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
        return [];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator The validator.
     * @return void
     * @throws ValidationException The validation exception.
     */
    public function failedValidation(Validator $validator)
    {
        if ($this->isApiRequest()) {
            $response = Controller::sendResponse(false, "Un ou plusieurs champs sont incorrects.", $validator->errors(), [], $this->all(), [], 404);
            throw (new ValidationException($validator, $response))->status(400);
        } else {
            return parent::failedValidation($validator);
        }
    }

    /**
     * Is api request.
     *
     * @return bool
     */
    public function isApiRequest(): bool
    {
        return 2 <= sizeof(explode('/api/', $this->getRequestUri()));
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array The messages.
     */
    public function messages(): array
    {
        $data = $this->validationData();
        $profileName = Controller::getProfileName($data['profile'] ?? 'STUDENT');

        return [
            'email.required' => __('Le champ adresse email :profileName est obligatoire.', ['profileName' => $profileName]),
            'email.string' => __('Le champ adresse email :profileName doit être une chaine de caractère.', ['profileName' => $profileName]),
            'email.email' => __('Le champ adresse email :profileName doit être une adresse mail valide.', ['profileName' => $profileName]),
            'email.max' => __('Le champ adresse email :profileName ne doit pas dépasser 255 caractères.', ['profileName' => $profileName]),
            'email.unique' => __('Le champ adresse email :profileName doit être une adresse mail unique (non utilisée). L\'adresse email choisie est déjà utiliser par un autre utilisateur du même profile.', ['profileName' => $profileName]),
            'email.exists' => __('Le champ adresse email :profileName doit être une adresse mail existant. L\'adresse email choisie est invalide.', ['profileName' => $profileName]),

            'profile.required' => __('Le champ profil :profileName est obligatoire.', ['profileName' => $profileName]),
            'profile.string' => __('Le champ profil :profileName doit être une chaine de caractère.', ['profileName' => $profileName]),
            'profile.max' => __('Le champ profil :profileName ne doit pas dépasser 255 caractères.', ['profileName' => $profileName]),
            'profile.in' => __('Le champ profil :profileName doit avoir une des valeurs suivantes : STUDENT, CONTRIBUTOR, ADMINISTRATOR. Le profil sélectionné n\'est pas valide.', ['profileName' => $profileName]),

            'has_default_password.boolean' => __('Le champ définir un mot de passe par défaut :profileName doit être vrai ou faux.', ['profileName' => $profileName]),

            'password.required' => __('Le champ mot de passe :profileName est obligatoire.', ['profileName' => $profileName]),
            'password.string' => __('Le champ mot de passe :profileName doit être une chaine de caractère.', ['profileName' => $profileName]),
            'password.max' => __('Le champ mot de passe :profileName ne doit pas dépasser 255 caractères.', ['profileName' => $profileName]),
            //'password.rules.password' => __('Le champ mot de passe :profileName ne correspond pas à celle du champ mot de passe.', ['profileName' => $profileName]),
            'password.confirmed' => __('Le champ mot de passe :profileName ne correspond pas à celle du champ mot de passe.', ['profileName' => $profileName]),

            'password_confirmation.required' => __('Le champ de confirmation du mot de passe :profileName est obligatoire.', ['profileName' => $profileName]),
            'password_confirmation.string' => __('Le champ de confirmation du mot de passe :profileName doit être une chaine de caractère.', ['profileName' => $profileName]),
            'password_confirmation.max' => __('Le champ de confirmation du mot de passe :profileName ne doit pas dépasser 255 caractères.', ['profileName' => $profileName]),
            'password_confirmation.same' => __('Le champ de confirmation du mot de passe :profileName doit correspondre au champ mot de passe.', ['profileName' => $profileName]),

            'terms_condition.required' => __('Le champ des conditions générales :profileName est obligatoire.', ['profileName' => $profileName]),
            'terms_condition.boolean' => __('Le champ des conditions générales :profileName doit être vrai ou faux.', ['profileName' => $profileName]),

            'validate_account_url.required' => __('Le champ lien de confirmation du compte :profileName est obligatoire.', ['profileName' => $profileName]),
            'validate_account_url.url' => __('Le champ lien de confirmation du compte :profileName doit être un lien ou un url.', ['profileName' => $profileName]),

            'token.required' => __('Le champ clé / jeton :profileName est obligatoire.', ['profileName' => $profileName]),
            'token.string' => __('Le champ clé / jeton :profileName doit être une chaine de caractère.', ['profileName' => $profileName]),
            'token.max' => __('Le champ clé / jeton :profileName ne doit pas dépasser 255 caractères.', ['profileName' => $profileName]),
            'token.exists' => __('Le champ clé / jeton :profileName doit être une clé existant. La clé choisie est invalide.', ['profileName' => $profileName]),

            'reset_password_url.required' => __('Le champ lien de réinitialisation de mot de passe du compte :profileName est obligatoire.', ['profileName' => $profileName]),
            'reset_password_url.url' => __('Le champ lien de réinitialisation de mot de passe du compte :profileName doit être un lien ou un url.', ['profileName' => $profileName])
        ];
    }

    /**
     * Prepare for validation.
     *
     * @return BaseRequest The base request.
     */
    protected function prepareForValidation(): BaseRequest
    {
        return $this->merge([
            'code' => !is_null($this->input('code')) ? array_replace(['-', ' ', 'é', 'è', 'ê', 'à', 'â', 'ç', '\'', '__'], ['', '_', 'E', 'E', 'E', 'A', 'A', 'C', '_', '_'], strtoupper($this->input('code'))) : str_replace(['-', ' ', 'é', 'è', 'ê', 'à', 'â', 'ç', '\'', '__'], ['', '_', 'E', 'E', 'E', 'A', 'A', 'C', '_', '_'], strtoupper($this->input('name'))),
            'number_per_page' => is_null($this->input('number_per_page')) ? 10 : $this->input('number_per_page'),
            'order_by' => is_null($this->input('order_by')) ? 'ASC' : $this->input('order_by')
        ]);
    }
}
