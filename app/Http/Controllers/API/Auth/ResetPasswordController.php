<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JsonResponseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ResetPasswordController extends Controller
{

    /**
     * Reset user password endpoint.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request)
    {
        try {
            // Validation rules for password reset request
            $rules = [
                'email' => ['required', 'email', 'exists:users,email', maxString()],
                'password' => ['required', 'min:8', maxString()],
                'password_confirmation' => ['required', 'same:password'],
                'verification_code' => ['required', 'min:6', 'max:6'],
            ];

            // Get data from the request
            $data = $request->all();

            $validator = Validator::make($data, $rules);

            // Check for validation errors
            if ($validator->fails()) {
                $message = 'Please fill the form correctly.';
                return JsonResponseService::validationErrorResponse($message, $validator->errors()->all());
            }

            $user = User::findByEmail($data['email']);

            // Validate the verification code for the user
            $verified = $user->validateVerificationCode($data['verification_code']);

            // If verification code is valid, update user password
            if ($verified) {
                $user->update([
                    'password' => Hash::make($data['password']),
                    'verification_code' => NULL,
                    'verification_code_generated_at' => NULL,
                ]);

                return JsonResponseService::successResponse('Password reset successfully. Please login to your account.');
            }

            // Return unauthorized response if verification code is invalid or expired
            return JsonResponseService::unauthorizedErrorResponse('The verification code is either invalid or expired. Please try again.');

        } catch (Throwable $th) {
            Log::info('**************************** Error in reset password process ********************************');
            Log::info('Error is on line: '. $th->getLine());
            Log::error($th->getMessage());
            return JsonResponseService::errorResponse('Something went wrong. Please try again.');
        }
    }
}
