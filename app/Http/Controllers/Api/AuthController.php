<?php

namespace App\Http\Controllers\Api;

use App\Events\UserAccountEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\SendEmailValidateAccountRequest;
use App\Http\Requests\Auth\SignInRequest;
use App\Http\Requests\Auth\SignUpRequest;
use App\Http\Requests\Auth\ValidateAccountRequest;
use App\Models\PasswordResetTokens;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Sign up a user.
     *
     * @param SignUpRequest $request The sign-up request.
     * @return JsonResponse $response The response.
     */
    public function signUp(SignUpRequest $request): JsonResponse
    {
        $userData = $request->validated();
        $userData['registration_number'] = uniqid('registration-');
        $userData['has_default_password'] = $request->boolean('has_default_password');

        $defaultPassword = '';
        if ($userData['has_default_password']) {
            $userData['password'] = $defaultPassword = $this->generate_random_password();
            $userData['has_default_password'] = 1;
        }

        $userData['password'] = Hash::make($userData['password']);

        $user = User::create($userData);

        try {
            $token = Str::random(64);
            PasswordResetTokens::create(['email' => $request->validated('email'), 'profile' => $request->validated('profile'), 'token' => $token, 'type' => 'VALIDATE-ACCOUNT']);
        } catch (QueryException) {
            $passwordResetToken = PasswordResetTokens::where('email', $request->validated('email'))->where('profile', $request->validated('profile'))->where('type', 'VALIDATE-ACCOUNT')->first();
            $token = $passwordResetToken->token;
        }

        // Notification création de compte.
        $signUpData['title'] = __('Création de compte sur :app-name', ['app-name' => config('app.name')]);
        $signUpData['message'] = __('Création de compte sur :app-name', ['app-name' => config('app.name')]);
        $signUpData['view'] = 'mails.auth.sign-up';
        $signUpData['token'] = $token;
        $signUpData['validate_account_url'] = $userData['validate_account_url'] ?? '';
        event(new UserAccountEvent($user, $signUpData));

        // Notification mot de passe par défaut.
        if ($user->has_default_password) {
            $signUpDefaultPasswordData['title'] = __('Mot de passe par défaut de votre compte sur :app-name', ['app-name' => config('app.name')]);
            $signUpDefaultPasswordData['message'] = __('Mot de passe par défaut de votre compte sur :app-name', ['app-name' => config('app.name')]);
            $signUpDefaultPasswordData['view'] = 'mails.auth.sign-up-default-password';
            $signUpDefaultPasswordData['default_password'] = $defaultPassword;
            event(new UserAccountEvent($user, $signUpDefaultPasswordData));
        }

        return $this->sendResponse(true, __('Inscription / enregistrement / ajout :profileName effectué(e) avec succès.', ['profileName' => $this::getProfileName($request->validated('profile'))]), [], [], $user->toArray(), 201);
    }

    /**
     * Send email validate account treatment controller.
     *
     * @param SendEmailValidateAccountRequest $request The send email validate account request.
     * @return JsonResponse $response The response.
     */
    public function sendEmailValidateAccount(SendEmailValidateAccountRequest $request): JsonResponse
    {
        $user = User::whereEmail($request->validated('email'))->whereProfile($request->validated('profile'))->whereNull('activated_at')->whereNull('verified_at')->whereNull('deleted_at')->first();

        //if (is_null($user)) {
        //    return $this->sendResponse(false, $this::API_DEFAULT_ERROR_FIELDS_MESSAGE, [], [], $request->validated(), 404);
        //}
        //
        //if (!is_null($user->activated_at) && !is_null($user->verified_at)) {
        //    return $this->sendResponse(true, __('Votre compte est déjà activé. Veuillez vous connecter.'), [], [], $user->toArray(), 201);
        //}

        $passwordResetToken = PasswordResetTokens::where('email', $request->validated('email'))->where('profile', $request->validated('profile'))->where('type', 'VALIDATE-ACCOUNT')->first();

        if (!is_null($passwordResetToken)) {
            $token = $passwordResetToken->token;
        } else {
            $token = Str::random(64);
            PasswordResetTokens::create(['email' => $request->validated('email'), 'profile' => $request->validated('profile'), 'token' => $token, 'type' => 'VALIDATE-ACCOUNT']);
        }

        // Notification création de compte.
        $sendEmailValidateAccountData['title'] = __('Création de compte sur :app-name', ['app-name' => config('app.name')]);
        $sendEmailValidateAccountData['message'] = __('Création de compte sur :app-name', ['app-name' => config('app.name')]);
        $sendEmailValidateAccountData['view'] = 'mails.auth.sign-up';
        $sendEmailValidateAccountData['token'] = $token;
        $sendEmailValidateAccountData['validate_account_url'] = $userData['validate_account_url'] ?? '';
        event(new UserAccountEvent($user, $sendEmailValidateAccountData));

        return $this->sendResponse(true, __('Le mail de validation de compte de l\'utilisateur a été envoyé avec succès.'), [], [], $user->toArray(), 201);
    }

    /**
     * Validate account treatment controller.
     *
     * @param ValidateAccountRequest $request The validate account request.
     * @return JsonResponse $response The response.
     */
    public function validateAccount(ValidateAccountRequest $request): JsonResponse
    {
        $validateAccountData = $request->validated();
        $user = User::whereEmail($validateAccountData['email'])->whereProfile($validateAccountData['profile'])->whereNull('activated_at')->whereNull('verified_at')->whereNull('deleted_at')->first();
        $validateAccountToken = PasswordResetTokens::whereEmail($validateAccountData['email'])->whereProfile($validateAccountData['profile'])->whereToken($validateAccountData['token']);

        //if (is_null($validateAccountToken->first()) || $validateAccountToken->first()->email != $user->email) {
        //    return $this->sendResponse(false, __('Le champ :attribute sélectionné / renseigné est invalide.', ['attribute' => 'token']), [], [], $validateAccountData, 404);
        //}

        $user->update(['verified_at' => now(), 'activated_at' => now(), 'email_verified_at' => now()]);

        $validateAccountToken->delete();

        // Notification de validation de compte.
        $dataValidateAccount['title'] = __('Validation de compte sur :app-name', ['app-name' => config('app.name')]);
        $dataValidateAccount['message'] = __('Validation de compte sur :app-name', ['app-name' => config('app.name')]);
        $dataValidateAccount['view'] = 'mails.auth.validate-account';
        $dataValidateAccount['validate_account_url'] = $userData['validate_account_url'] ?? '';
        event(new UserAccountEvent($user, $dataValidateAccount));

        return $this->sendResponse(true, __('Votre compte a été validé. Vous pouvez vous connecter.'), [], [], $user->toArray(), 201);
    }

    /**
     * Sign in a user.
     *
     * @param SignInRequest $request The sign in request.
     * @return JsonResponse $response The response.
     * @throws ValidationException The validation exception.
     */
    public function signIn(SignInRequest $request): JsonResponse
    {
        $request->authenticate();

        // Notification d'une nouvelle connexion.
        $user = User::whereEmail($request->validated('email'))->whereProfile($request->validated('profile'))->first();
        $dataSignIn['title'] = __('Nouvelle connexion sur :app-name', ['app-name' => config('app.name')]);
        $dataSignIn['message'] = __('Nouvelle connexion sur :app-name', ['app-name' => config('app.name')]);
        $dataSignIn['view'] = 'mails.auth.sign-in';
        event(new UserAccountEvent($user, $dataSignIn));

        $user = Auth::guard($this::getGuard($request->validated('profile')))->user();
        $user_data = $user->toArray();
        $user_data['token'] = $user->createToken('me-api-sign-in-token', [$user_data['profile']], now()->addMinutes(60 * 24))->plainTextToken;

        return $this->sendResponse(true, __('Authentification / connexion effectuée avec sucès.'), [], [], $user_data, 200);
    }

    /**
     * Forgot password.
     *
     * @param ForgotPasswordRequest $request The forgot password request.
     * @return JsonResponse $response The response.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::whereEmail($request->validated('email'))->whereProfile($request->validated('profile'))->whereNotNull('activated_at')->whereNotNull('verified_at')->whereNull('deleted_at')->first();

        //if (is_null($user)) {
        // return $this->sendResponse(false, __('Le champ :attribute sélectionné / renseigné est invalide.', ['attribute' => 'adresse mail']), [], [], $validateAccountData, 404);
        //}

        $oldToken = PasswordResetTokens::whereEmail($request->validated('email'))->whereProfile($request->validated('profile'))->whereType('FORGOT-PASSWORD')->first();

        if (!is_null($oldToken)) {
            $token = $oldToken->token;
        } else {
            $token = Str::random(64);
            PasswordResetTokens::create(['email' => $request->validated('email'), 'profile' => $request->validated('profile'), 'token' => $token, 'type' => 'FORGOT-PASSWORD']);
        }

        // Notification de mot de passe oublié.
        $dataForgotPassword['title'] = __('Mot de passe oublier sur :app-name', ['app-name' => config('app.name')]);
        $dataForgotPassword['message'] = __('Mot de passe oublier sur :app-name', ['app-name' => config('app.name')]);
        $dataForgotPassword['view'] = 'mails.auth.forgot-password';
        $dataForgotPassword['token'] = $token;
        $dataForgotPassword['reset_password_url'] = $request->validated('reset_password_url') ?? '';
        event(new UserAccountEvent($user, $dataForgotPassword));

        return $this->sendResponse(true, __('Le mail de récupération de mot de passe de compte de l\'utilisateur a été envoyé avec succès.'), [], [], $user->toArray(), 201);
    }

    /**
     * Reset password.
     *
     * @param Request $request The request.
     * @return JsonResponse $response The response.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:strict|max:255',
            'profile' => 'required|in:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR',
            'token' => 'required|numeric',
            'password' => 'required|min:8|max:255|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%])(?=.{8,}).*$/',
        ], [
            'email.required' => 'Le champ email de l\'utilisateur est obligatoire.',
            'email.email' => 'Le champ email de l\'utilisateur doit être une adresse mail valide.',
            'email.max' => 'Le champ email de l\'utilisateur ne doit pas dépasser 255 caractères.',

            'profile.required' => 'Le champ profil de l\'utilisateur est obligatoire.',
            'profile.in' => 'Le champ profil de l\'utilisateur doit avoir une des valeurs suivantes : STUDENT, TEACHER, SCHOOL, ADMINISTRATOR.',

            'token.numeric' => 'Le champ clé de réinitialisation (token) du mot de passe de l\'utilisateur ne doit comporter que des chiffres.',
            'token.size' => 'Le champ clé de réinitialisation (token) du mot de passe de l\'utilisateur ne doit pas dépasser 4 caractères (des chiffres).',

            'password.required' => 'Le champ mot de passe de l\'utilisateur est obligatoire.',
            'password.min' => 'Le champ mot de passe de l\'utilisateur doit contenir au moins 8 caractères.',
            'password.max' => 'Le champ mot de passe de l\'utilisateur ne doit pas dépasser 255 caractères.',
            'password.regex' => 'Le champ mot de passe de l\'utilisateur doit respect les règles suivantes : au minimum huit caractères dont des lettres minuscules ou majuscules, au moins un chiffre, des caractères spéciaux comme par exemple : !, $, #, or %.'
        ]);
        if ($validator->fails()) {
            return $this->sendResponse(false, $this::API_DEFAULT_ERROR_FIELDS_MESSAGE, $validator->errors(), [], $request->all(), 400);
        }
        try {
            $reset_password_data = $validator->validated();

            $user = User::where([
                ['email', '=', $reset_password_data['email']],
                ['profile', '=', $reset_password_data['profile']],
                ['is_activated', '=', 1]
            ])->first();

            if (is_null($user)) {
                return $this->sendResponse(false, $this::API_DEFAULT_ERROR_FIELDS_MESSAGE, [
                    'email' => ['Le champ email de l\'utilisateur n\'existe pas.']
                ], [], $request->all(), 400);
            }
            $password_reset_token = PasswordResetTokens::where([
                ['email', '=', $user->email],
                ['user_id', '=', $user->id],
                //['token', '=', $reset_password_data['token']],
                ['is_activated', '=', 1]
            ])->first();
            if (is_null($password_reset_token) || $password_reset_token->token != $reset_password_data['token']) {
                return $this->sendResponse(false, $this::API_DEFAULT_ERROR_FIELDS_MESSAGE, [
                    'token' => ['La clé de réinitialisation (token) du mot de passe de l\'utilisateur n\'existe pas.']
                ], [], $request->all(), 400);
            }
            $user->password = bcrypt($reset_password_data['password']);
            $user->save();
            $user->tokens()->delete();
            $password_reset_token->is_activated = 0;
            $password_reset_token->save();
            $password_reset_token->delete();
            $reset_password_mail_data = array_merge($user->toArray(), array('title' => 'Mot de passe oublier'));
            $this->send_mail($user->email, 'Réinitialisation de mot de passe de compte', 'mail/account/change-password', $reset_password_mail_data);

            return $this->sendResponse(true, 'La réinitialisation de mot de passe a été effectuée avec succès. ', [], [], ['user' => $user->toArray()]);
        } catch (ValidationException $e) {
            return $this->sendResponse(false, $e->getMessage(), [], [], $request->all(), 400);
        }
    }

    /**
     * Sign out  a user.
     *
     * @param Request $request The request.
     * @return JsonResponse $response The response.
     */
    public static function signOut(Request $request): JsonResponse
    {
        $current_access_token = $request->user()->currentAccessToken();
        if ($current_access_token) {
            $current_access_token->delete();
        }
        return self::sendResponse(true, 'L\'utilisateur est déconnecté avec succès.');
    }

    /**
     * Generate random password.
     *
     * @return string $password The password.
     */
    private function generate_random_password(): string
    {
        // Set random length for password
        $password_length = rand(8, 16);
        $password = '';
        for ($i = 0; $i < $password_length; $i++) {
            $password .= chr(rand(32, 126));
        }
        return $password;
    }
}
