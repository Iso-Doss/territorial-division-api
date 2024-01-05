<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LocalizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Resource doesn't exist endpoint.
Route::fallback(function (Request $request) {
    return Controller::sendResponse(false, 'La ressource n\'existe pas ou le verbe / la methode utilisé(e) n\'est pas autorisé(e).', [], [], $request->all(), 404);
});

// Unauthorized endpoint.
Route::get('/unauthorized', function (Request $request) {
    return Controller::sendResponse(false, 'L\'utilisateur n\'est pas autorisé à accéder à cette ressource.', [], [], $request->all(), 401);
})->name('unauthorized');

// Get language endpoint.
Route::get('locale', [LocalizationController::class, 'getLang'])->name('get-lang');

// Change language endpoint.
Route::get('locale/{lang}', [LocalizationController::class, 'setLang'])->name('set-lang');

// Auth endpoints.
Route::controller(AuthController::class)->name('auth.')->prefix('/auth')->group(function () {
    Route::post('/sign-up', 'signUp')->name('sign-up');
    Route::post('/send-email-validate-account', 'sendEmailValidateAccount')->name('send-email-validate-account');
    Route::get('/validate-account/{email}/{profile}/{token}', 'validateAccount')->name('validate-account');
    Route::post('/sign-in', 'signIn')->name('sign-in');
    Route::post('/password/forgot', 'forgotPassword')->name('forgot-password');
    Route::post('/password/reset/{email}/{profile}/{token}', 'resetPassword')->name('reset-password');
    Route::post('/sign-out', 'signOut')->name('sign-out')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);

    //Route::post('/validation/request', 'validationRequest')->name('validation-request');
    //Route::post('/validate', 'validation')->name('validation');
    //Route::post('/validation', 'validation')->name('validation');
});

// Token endpoints.
Route::controller(TokenController::class)->name('token.')->prefix('/token')->group(function () {
    Route::post('/', 'create')->name('create')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::get('/', 'tokens')->name('tokens')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::get('/check', 'check')->name('check')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::delete('/{id}', 'delete')->name('delete')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::delete('/', 'delete_tokens')->name('delete-all-tokens')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
});

// User endpoints.
Route::controller(UserController::class)->name('user.')->prefix('/user')->group(function () {
    Route::patch('/{id}', 'update')->name('update')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::patch('/enable-or-disable/{id}', 'enable_or_disable')->name('enable-disable')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    // Route::patch('/change-password/{id}', 'change_password')->name('change-password')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::patch('/password/change/{id}', 'change_password')->name('change-password')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::patch('/generate-student-registration-number/{id}', 'generate_student_registration_number')->name('generate-student-registration-number')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::delete('/{id}', 'delete')->name('delete')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::post('/get-filter', 'get_filter')->name('get-filter')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
});

// Country endpoints.
Route::controller(CountryController::class)->name('country.')->prefix('/country')->group(function () {
    Route::post('/', 'add')->name('add')->middleware(['auth:sanctum', 'ability:ADMINISTRATOR']);
    Route::patch('/{id}', 'update')->name('update')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:ADMINISTRATOR']);
    Route::patch('/enable-or-disable/{id}', 'enable_or_disable')->name('enable-or-disable')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:ADMINISTRATOR']);
    Route::delete('/{id}', 'delete')->name('delete')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:ADMINISTRATOR']);
    Route::post('/get-filter', 'get_filter')->name('get-filter');
});

// Notification endpoints.
Route::controller(NotificationController::class)->name('notification.')->prefix('/notification')->group(function () {
    Route::post('/', 'add')->name('add')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::patch('/{id}', 'update')->name('update')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::patch('/change-status/{id}', 'change_status')->name('change-status')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::delete('/{id}', 'delete')->name('delete')->where('id', '[0-9]+')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
    Route::post('/get-filter', 'get_filter')->name('get-filter')->middleware(['auth:sanctum', 'ability:STUDENT,TEACHER,SCHOOL,ADMINISTRATOR']);
});
