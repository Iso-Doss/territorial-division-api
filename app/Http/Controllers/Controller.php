<?php

namespace App\Http\Controllers;

use App\Events\UserAccountEvent;
//use App\Http\Controllers\Api\UserProfileController;
use App\Http\Requests\Auth\SignUpRequest;
use App\Models\User;
//use App\Services\ChromePdfService;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     description="API for CFP Le Savoir-Faire : Centre de Formation Professionnelle",
 *     version="1.0.0",
 *     title="CFP Le Savoir-Faire : Centre de Formation Professionnelle"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Default message for api error fields.
     */
    const API_DEFAULT_ERROR_FIELDS_MESSAGE = 'Oups !!! Un ou plusieurs champ(s) sont incorrect(s).';

    /**
     * Others get filter fields.
     */
    const OTHERS_GET_FILTER_FIELDS = array('offset', 'limit', 'request_type', 'column_list', 'equiv_list', 'cascade');

    /**
     * The profile.
     *
     * @var string $profile The profile.
     */
    protected string $profile = 'customer';

    /**
     * Controller construct.
     *
     * @param string $profile The profile.
     */
    public function __construct(string $profile = 'customer')
    {
        $this->profile = $profile;
        View::share('profile', $this->profile);
    }

    /**
     * Upload image.
     *
     * @param Request $request The request.
     * @param string $path The path.
     * @param string $fieldName The field name.
     * @return bool|string The upload image.
     */
    public static function uploadImage(Request $request, string $path, string $fieldName = 'image'): bool|string
    {
        /** @var UploadedFile | null $image */
        //$image = $request->validated($fieldName);
        /** @var UploadedFile | null $image */
        $image = $request->file($fieldName);
        if (null === $image || $image->getError()) {
            return false;
        }
        $image->store($path, 'cfp');
        return $image->store($path, 'public');
    }

    /**
     * Get the profile guard.
     *
     * @param string $profile The profile.
     * @return string $guard The guard.
     */
    public static function getGuard(string $profile): string
    {
        $profiles = UserProfileController::getCollections(['activated_at' => 'ENABLE', 'number_per_page' => 100])->pluck('code')->toArray();
        $guard = 'student';

        if (in_array($profile, $profiles)) {
            $guard = strtolower($profile);
        }

        return $guard;
    }

    /**
     * Get the profile name.
     *
     * @param string $profile The profile.
     * @return string The profile name.
     */
    public static function getProfileName(string $profile): string
    {
        // RDC-ID : Mettre a jour la fonction.
        if ('CUSTOMER' == $profile) {
            $profileName = 'de l\'utilisateur';
        } elseif ('CONTRIBUTOR' == $profile) {
            $profileName = 'du contributeur';
        } elseif ('ADMINISTRATOR' == $profile) {
            $profileName = 'de l\'administrateur';
        } else {
            $profileName = 'de l\'utilisateur';
        }

        return $profileName;
    }

    /**
     * Send a success response method.
     *
     * @param bool $success The success.
     * @param string $message The message
     * @param array|MessageBag $errors The errors.
     * @param array $warnings The warnings.
     * @param array $input The input.
     * @param array|LengthAwarePaginator|Collection $data The data.
     * @param int $status_code The status code.
     * @return JsonResponse $response The response.
     */
    public static function sendResponse(bool $success = true, string $message = '', array|MessageBag $errors = [], array $warnings = [], array $input = [], array|LengthAwarePaginator|Collection $data = [], int $status_code = 200): JsonResponse
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'errors' => $errors,
            'warnings' => $warnings,
            'input' => $input,
            'output' => $data,
            'data' => $data,
        ];
        return response()->json($response, $status_code);
    }

    /**
     * Get ressources by a filters.
     *
     * @param Request $request The request.
     * @return JsonResponse $response The response.
     */
    public static function globalFilter(Request $request, string $table): JsonResponse
    {
        //Paginate
        //https://laravel.com/docs/10.x/queries
        //https://stackoverflow.com/questions/23059918/laravel-get-base-url
        //https://github.com/barryvdh/laravel-dompdf
        //https://techvblogs.com/blog/multiple-authentication-guards-laravel-9
        //https://fideloper.com/laravel-database-transactions
        //https://dev.to/mahmudulhsn/update-existing-table-with-migration-without-losing-in-data-in-laravel-fb1
        ///opt/cpanel/ea-php82/root/usr/bin/php artisan migrate:refresh --seed
        //Export data
        //https://github.com/spatie/laravel-personal-data-export
        //https://laravel-news.com/laravel-personal-data-export
        //https://github.com/spatie/laravel-export
        //https://arjunamrutiya.medium.com/a-step-by-step-guide-to-importing-and-exporting-csv-files-using-laravel-without-packages-4274b3ed03df
        //https://dev.to/techsolutionstuff/how-to-export-csv-file-in-laravel-example-12ip
        //https://www.itsolutionstuff.com/post/laravel-52-user-acl-roles-and-permissions-with-middleware-using-entrust-from-scratch-tutorialexample.html
        //https://www.itsolutionstuff.com/post/laravel-10-rest-api-with-passport-authentication-tutorialexample.html
        //https://www.itsolutionstuff.com/post/laravel-10-rest-api-authentication-using-sanctum-tutorialexample.html
        //https://www.itsolutionstuff.com/post/laravel-10-send-email-using-queue-exampleexample.html
        //https://www.itsolutionstuff.com/post/laravel-10-cron-job-task-scheduling-tutorialexample.html
        //https://www.itsolutionstuff.com/post/laravel-10-one-to-one-relationship-exampleexample.html
        //https://www.itsolutionstuff.com/post/laravel-10-multi-auth-create-multiple-authentication-in-laravelexample.html
        //https://www.itsolutionstuff.com/post/laravel-convert-array-to-query-string-exampleexample.html
        //https://www.itsolutionstuff.com/post/laravel-10-react-js-auth-scaffolding-tutorialexample.html
        //https://www.itsolutionstuff.com/post/laravel-react-js-crud-application-tutorialexample.html
        //https://www.itsolutionstuff.com/post/laravel-10-user-roles-and-permissions-tutorialexample.html
        //https://docs.laravel-excel.com/3.1/exports


        $ressourcesDb = DB::table($table);
        $ressources = [];
        $data = $request->all();
        $ressource_columns = Schema::getColumnListing($table);
        $attributes = array_merge($ressource_columns, self::OTHERS_GET_FILTER_FIELDS);
        $wheres = [];
        $wheres_in = [];
        $wheres_not_in = [];
        $wheres_between = [];
        $wheres_not_between = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $attributes)) {
                unset($data[$key]);
            }

            if (isset($data[$key]) && !in_array($key, self::OTHERS_GET_FILTER_FIELDS)) {
                if (is_array($value) && !empty($value['value'])) {
                    if (!is_array($value['value']) && (empty($value['comparator']) || !in_array($value['comparator'], array('=', '<>', '!=', '<=', '>=', '>', '<')))) {
                        $value['comparator'] = '=';
                    } elseif (is_array($value['value']) && (empty($value['comparator']) || !in_array($value['comparator'], array('IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN')))) {
                        $value['comparator'] = 'IN';
                    }
                    if (!is_array($value['value'])) {
                        $wheres[] = [$key, $value['comparator'], $value['value']];
                    } else {
                        if ($value['comparator'] == 'IN') {
                            $wheres_in[] = [$key, $value['value']];
                        } elseif ($value['comparator'] == 'NOT IN') {
                            $wheres_not_in[] = [$key, $value['value']];
                        } elseif ($value['comparator'] == 'BETWEEN') {
                            $wheres_between[] = [$key, $value['value']];
                        } elseif ($value['comparator'] == 'NOT BETWEEN') {
                            $wheres_not_between[] = [$key, $value['value']];
                        }
                    }
                } else {
                    $wheres[] = [$key, '=', $value];
                }
            }
        }

        if (!empty($data['column_list']) && !is_array($data['column_list'])) {
            $data['column_list'] = explode(',', str_replace(' ', '', $data['column_list']));
        }

        if (!empty($data['column_list']) && is_array($data['column_list'])) {
            $not_attributes = array_diff($data['column_list'], $ressource_columns);
            foreach ($data['column_list'] as $key => $column) {
                if (in_array($column, $not_attributes)) {
                    unset($data['column_list'][$key]);
                }
            }
        }

        if (!empty($data['column_list'])) {
            $ressourcesDb->select(DB::raw(implode(',', $data['column_list'])));
        }

        if (!empty($wheres)) {
            $ressourcesDb->where($wheres);
        }
        if (!empty($wheres_in)) {
            foreach ($wheres_in as $where_in) {
                $ressourcesDb->whereIn($where_in[0], $where_in[1]);
            }
        }
        if (!empty($wheres_not_in)) {
            foreach ($wheres_not_in as $where_not_in) {
                $ressourcesDb->whereNotIn($where_not_in[0], $where_not_in[1]);
            }
        }
        if (!empty($wheres_between)) {
            foreach ($wheres_between as $where_between) {
                $ressourcesDb->whereBetween($where_between[0], $where_between[1]);
            }
        }
        if (!empty($wheres_not_between)) {
            foreach ($wheres_not_between as $where_not_between) {
                $ressourcesDb->whereNot($where_not_between[0], $where_not_between[1]);
            }
        }

        if (!empty($data['limit'])) {
            $ressourcesDb->limit($data['limit']);
        }

        if (!empty($data['offset'])) {
            $ressourcesDb->offset($data['offset']);
        }

        if (!empty($data['order'])) {
            $ressourcesDb->orderBy($data['order']);
        }

        if (isset($data['request_type']) && 'count' == $data['request_type']) {
            $ressources['count'] = $ressourcesDb->count();
        } else {
            $ressources = $ressourcesDb->get()->toArray();
        }

        return self::sendResponse(true, 'La liste des ressources demandées.', [], [], $ressources);
    }

    /**
     * Get web controller response.
     *
     * @param JsonResponse $response The request.
     * @param string $route The route name.
     * @param array $parameters The route parameters.
     * @return RedirectResponse
     */
    public
    function webControllerResponse(JsonResponse $response, string $route, array $parameters = []): RedirectResponse
    {
        $responseData = json_decode($response->content(), true);

        return redirect()->route($route, $parameters)
            ->with(['success' => ($responseData['success']) ? $responseData['message'] : '', 'warnings' => (!$responseData['warnings']) ? $responseData['warnings'] : '', 'error' => (!$responseData['success']) ? $responseData['message'] : '', 'input' => $responseData['input']])
            ->withErrors($responseData['errors'])
            ->withInput($responseData['input']);
    }

    /**
     * Generate random password.
     *
     * @return string $password The password.
     * @throws Exception The exception.
     */
    public static function generateRandomPassword(): string
    {
        // Set random length for password
        $password_length = random_int(8, 16);
        $password = '';
        for ($i = 0; $i < $password_length; $i++) {
            $password .= chr(random_int(32, 126));
        }
        return $password;
    }

    /**
     * Create user.
     *
     * @param SignUpRequest $request The sign-up request.
     * @return User|Model
     * @throws Exception The exception.
     */
    public static function createUser(SignUpRequest $request): User|Model
    {
        $signUpInput = $request->validated();
        $signUpInput['registration_number'] = uniqid('registration-');
        $signUpInput['has_default_password'] = $request->boolean('has_default_password');

        $defaultPassword = '';
        if ($signUpInput['has_default_password']) {
            $signUpInput['password'] = $defaultPassword = self::generateRandomPassword();
            $signUpInput['has_default_password'] = 1;
        }

        $signUpInput['password'] = Hash::make($signUpInput['password']);

        $user = User::create($signUpInput);

        //try {
        //    $token = Str::random(64);
        //    PasswordResetTokens::create(['email' => $signUpInput['email'], 'profile' => $signUpInput['profile'], 'token' => $token, 'type' => 'VALIDATE-ACCOUNT']);
        //} catch (QueryException) {
        //    $passwordResetToken = PasswordResetTokens::whereEmail($signUpInput['email'])->whereProfile($signUpInput['profile'])->whereType('VALIDATE-ACCOUNT')->first();
        //    $token = $passwordResetToken->token;
        //}

        // Notification création de compte.
        $signUpMailData['title'] = __('Création de compte sur :app-name', ['app-name' => config('app.name')]);
        $signUpMailData['message'] = __('Création de compte sur :app-name', ['app-name' => config('app.name')]);
        $signUpMailData['view'] = 'mails.auth.sign-up';
        //$signUpMailData['token'] = $token;
        $signUpMailData['validate_account_url'] = $signUpInput['validate_account_url'] ?? '';
        event(new UserAccountEvent($user, $signUpMailData));

        // Notification mot de passe par défaut.
        if ($user->has_default_password) {
            $signUpDefaultPasswordMailData['title'] = __('Mot de passe par défaut de votre compte sur :app-name', ['app-name' => config('app.name')]);
            $signUpDefaultPasswordMailData['message'] = __('Mot de passe par défaut de votre compte sur :app-name', ['app-name' => config('app.name')]);
            $signUpDefaultPasswordMailData['view'] = 'mails.auth.sign-up-default-password';
            $signUpDefaultPasswordMailData['default_password'] = $defaultPassword;
            event(new UserAccountEvent($user, $signUpDefaultPasswordMailData));
        }
        return $user;
    }

    /**
     * Generate student registration number.
     *
     * @param string $siteCode The site code.
     * @param int $studentId The student id.
     * @return string
     */
    public static function generateStudentRegistrationNumber(string $siteCode, int $studentId): string
    {
        $studentIdCode = '';
        $studentIdLength = strlen((string)$studentId);
        if ($studentIdLength < 6) {
            $studentIdCode = str_repeat('0', 6 - $studentIdLength);
        }

        return $siteCode . '-' . now()->year . '-' . $studentIdCode . $studentId;
    }

    //public static function generateStudentRegistrationNumber(string $siteCode, int $backSchoolYear, int $studentId): string
    //{
    //    $studentIdCode = '';
    //    $studentIdLength = strlen((string)$studentId);
    //    if ($studentIdLength < 6) {
    //        $studentIdCode = str_repeat('0', 6 - $studentIdLength);
    //    }
    //
    //    return $siteCode . '-' . $backSchoolYear . '-' . $studentIdCode . $studentId;
    //}

    /**
     * Make the transaction.
     *
     * @param Closure $callback The callbacks.
     * @return void
     */
    public static function transaction(Closure $callback)
    {

    }

    /**
     * Generate document.
     *
     * @param array $data The data.
     * @param string $documentFileNamePath The document file name path.
     * @param string $documentFileName The document file name.
     * @param string $outputPath The output path.
     * @param string $storageDiskName The storage disk name.
     * @return array $output The output.
     */
    public static function generateDocument(array $data, string $documentFileNamePath, string $documentFileName, string $outputPath, string $storageDiskName = 'public'): array
    {
        $output = [];
        $outputDir = Storage::disk($storageDiskName)->path('') . $outputPath;
        ob_start();
        echo view($documentFileNamePath, ['data' => $data])->render();
        $html = ob_get_clean();
        if (!is_dir($outputDir)) {
            if (!self::mkdir_r($outputDir)) {
                $output['status'] = false;
                $output['message'] = __('Échec lors de la création des dossiers.');
                return $output;
            }
        }

        $chromePdf = new ChromePdfService();
        $chromePdf->output($outputDir . $documentFileName);
        $chromePdf->generateFromHtml($html);
        if (file_exists($outputDir . $documentFileName)) {
            chmod($outputDir . $documentFileName, 0777);
        }
        $output['status'] = true;
        $output['message'] = 'Pdf généré avec succès.';
        $output['document_file_name'] = $outputPath . $documentFileName;

        return $output;
    }

    /**
     * Create directory recursively.
     *
     * @param string $dirName
     * @param int $rights
     * @param string $dir_separator
     * @return bool
     */
    public static function mkdir_r(string $dirName, int $rights = 0777, string $dir_separator = DIRECTORY_SEPARATOR): bool
    {
        $dirs = explode($dir_separator, $dirName);
        $dir = '';
        $created = false;
        foreach ($dirs as $part) {
            $dir .= $part . $dir_separator;
            if (!is_dir($dir) && strlen($dir) > 0) {
                $created = mkdir($dir, $rights);
            }
        }
        return $created;
    }
}
