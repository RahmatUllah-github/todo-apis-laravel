<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JsonResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class LoginController extends Controller
{

    /**
     * User login endpoint.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        try {
            // Validation rules for login request
            $rules = [
                'email' => ['required', 'email', 'exists:users,email', maxString()],
                'password' => ['required', 'min:8', maxString()],
            ];

            $data = $request->all();

            // Validate the incoming request data
            $validator = Validator::make($data, $rules);

            // Check for validation errors
            if ($validator->fails()) {
                $message = 'Please fill the form correctly.';
                return JsonResponseService::validationErrorResponse($message, $validator->errors()->all());
            }

            // Find user by email
            $user = User::findByEmail($data['email']);

            // Check if the provided password matches the hashed password in the database
            if (! Hash::check($data['password'], $user->password)) {
                return JsonResponseService::unauthorizedErrorResponse('The given credentials do not match.');
            }

            // Check if the user's email is verified
            if ($user->hasEmailVerified()) {
                // Generate a token for the authenticated user
                $responseData = [
                    'token' => $user->createToken(config('app.name'))->plainTextToken,
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];

                return JsonResponseService::successResponse('Logged in successfully.', $responseData);
            }

            // Return unauthorized response if email is not verified
            return JsonResponseService::unauthorizedErrorResponse('Please verify your email first');
        } catch (Throwable $th) {
            Log::info('**************************** Error in login process ********************************');
            Log::info('Error is on line: '. $th->getLine());
            Log::error($th->getMessage());
            return JsonResponseService::errorResponse('Something went wrong. Please try again.');
        }
    }
}
