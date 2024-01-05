<?php

namespace App\Http\Controllers;

use App\Events\UserAccountEvent;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendEmailValidateAccountRequest;
use App\Http\Requests\Auth\SignInRequest;
use App\Http\Requests\Auth\SignUpRequest;
use App\Models\PasswordResetTokens;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    /**
     * Forgot password treatment controller.
     *
     * @param ForgotPasswordRequest $request The forgot password request.
     * @return RedirectResponse The redirect response.
     */

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get current user",
     *     description="Returns information about the current user if the request is authenticated",
     *     @OA\Response(
     *         response=200,
     *         description="Everything OK"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access Denied"
     *     )
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request): RedirectResponse
    {
        $user = User::whereEmail($request->validated('email'))->whereProfile($request->validated('profile'))->first();

        if (is_null($user)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => trans('passwords.user')]);
        }

        $oldToken = PasswordResetTokens::where('email', $request->validated('email'))->where('profile', $request->validated('profile'))->where('type', 'FORGOT-PASSWORD')->first();

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
        event(new UserAccountEvent($user, $dataForgotPassword));

        return back()->withInput($request->only('email'))->with('success', __('passwords.sent'));
    }

    /**
     * Reset password form controller.
     *
     * @return View The reset password form view.
     */
    public function resetPasswordForm(string $email, string $token): View
    {
        return view($this->profile . '.auth.reset-password', ['email' => $email, 'token' => $token]);
    }

    /**
     * Reset password treatment controller.
     *
     * @param ResetPasswordRequest $request The reset password request.
     * @return RedirectResponse The redirect response.
     */
    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $user = User::whereEmail($request->validated('email'))->whereProfile($request->validated('profile'))->first();

        $passwordResetToken = PasswordResetTokens::where('email', $request->validated('email'))->where('profile', $request->validated('profile'))->where('token', $request->validated('token'))->where('type', 'FORGOT-PASSWORD');

        if (is_null($passwordResetToken->first()) || $passwordResetToken->first()->email != $user->email) {
            return back()->withInput($request->only('email', 'token', 'profile'))->withErrors(['token' => __('Le champ :attributes sélectionné / renseigné est invalide.', ['attributes' => 'token'])]);
        }

        $user->update(['password' => Hash::make($request->validated('password'))]);

        $passwordResetToken->delete();

        // Notification de réinitialisation du mot de passe.
        $dataResetPassword['title'] = __('Réinitialisation du mot de passe sur :app-name', ['app-name' => config('app.name')]);
        $dataResetPassword['message'] = __('Réinitialisation du mot de passe sur :app-name', ['app-name' => config('app.name')]);
        $dataResetPassword['view'] = 'mails.auth.reset-password';
        event(new UserAccountEvent($user, $dataResetPassword));

        return to_route($this->profile . '.auth.sign-in')->with(['success' => trans('Votre mot de passe a été changé ! Vous pouvez vous connectez.')]);
    }

    /**
     * Sign out treatment controller.
     *
     * @return RedirectResponse The redirect response.
     */
    public function signOut(): RedirectResponse
    {
        Auth::guard($this->getGuard(Auth::user()->profile))->logout();

        return redirect()->route($this->profile . '.auth.sign-in')->with(['success' => 'Vous êtes déconnectez.']);
    }

}
