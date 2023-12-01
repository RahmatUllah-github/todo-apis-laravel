<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JsonResponseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Throwable;

class RegisterController extends Controller
{
    /**
     * User registration endpoint.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        try {
            // Validation rules
            $rules = [
                'name' => ['required', 'string', maxString()],
                'email' => ['required', 'email', 'unique:users,email', maxString()],
                'password' => ['required', 'min:8', maxString()],
                'password_confirmation' => ['required', 'same:password']
            ];

            $data = $request->all();

            // Validate the incoming request data
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $message = 'Please fill the form correctly.';
                return JsonResponseService::validationErrorResponse($message, $validator->errors()->all());
            }

            // Generate a verification code
            $verificationCode = verificationCode();

            $data['verification_code'] = Hash::make($verificationCode);
            $data['verification_code_generated_at'] = Carbon::now();

            // Create a new user
            $user = User::create($data);

            // Prepare data for the email event
            $dataForEmail = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            // Fire UserRegistered event
            event(new UserRegistered($dataForEmail, $verificationCode));

            return JsonResponseService::successResponse('Please verify your email to continue.');

        } catch (Throwable $th) {
            if ($user) {
                $user->delete(); // if user is created and then something went wrong then delete
            }

            Log::info('**************************** Error in User registration ********************************');
            Log::info('Error is on line: '. $th->getLine());
            Log::error($th->getMessage());
            return JsonResponseService::errorResponse('Something went wrong. Please try again.');
        }
    }
}
