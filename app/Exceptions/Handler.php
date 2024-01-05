<?php

namespace App\Exceptions;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use InvalidArgumentException;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (QueryException $e, $request) {
            if ($request->is('api/*')) {
                //dd('QueryException', $e, $request);
                return Controller::sendResponse(false, $e->getMessage(), [], [], [], [], 404);
            } else {
                return false;
            }
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                //dd('AuthorizationException', $e, $request);
                return Controller::sendResponse(false, $e->getMessage(), [], [], [], [], 404);
            } else {
                return false;
            }
        });

        $this->renderable(function (MissingAbilityException $e, $request) {
            if ($request->is('api/*')) {
                //dd('MissingAbilityException', $e, $request);
                return Controller::sendResponse(false, $e->getMessage(), [], [], [], [], 404);
            } else {
                return false;
            }
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                //dd('AuthenticationException', $e, $request);
                return Controller::sendResponse(false, __('Vous n\'êtes pas authentifié.') . $e->getMessage(), [], [], [], [], 404);
            } else {
                return false;
            }
        });

        $this->renderable(function (MethodNotAllowedException $e, $request) {
            if ($request->is('api/*')) {
                //dd('MethodNotAllowedException', $e, $request);
                return Controller::sendResponse(false, $e->getMessage(), [], [], [], [], 404);
            } else {
                return false;
            }
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                //dd('MethodNotAllowedException', $e, $request);
                return Controller::sendResponse(false, $e->getMessage(), [], [], [], [], 404);
            } else {
                return false;
            }
        });

        $this->renderable(function (InvalidArgumentException $e, $request) {
            if ($request->is('api/*')) {
                //dd('MethodNotAllowedException', $e, $request);
                return Controller::sendResponse(false, $e->getMessage(), [], [], [], [], 404);
            } else {
                return false;
            }
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                //dd('MethodNotAllowedException', $e, $request);
                return Controller::sendResponse(false, $e->getMessage(), [], [], [], [], 404);
            } else {
                return false;
            }
        });
    }
}
