<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationMail;
use App\Models\User;
use App\Services\JsonResponseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class VerificationController extends Controller
{

    /**
     * Verify user email endpoint.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyEmail(Request $request)
    {
        try {
            // Validation rules for email verification request
            $rules = [
                'email' => ['required', 'email', 'exists:users,email', maxString()],
                'verification_code' => ['required', 'min:6', 'max:6'],
            ];

            $data = $request->all();

            // Validate the incoming request data
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $message = 'Please fill the form correctly.';
                return JsonResponseService::validationErrorResponse($message, $validator->errors()->all());
            }

            $user = User::findByEmail($data['email']);

            // Validate the verification code for the user
            $verified = $user->validateVerificationCode($data['verification_code']);

            // If verification code is valid, update user email verification status
            if ($verified) {
                $user->update([
                    'email_verified_at' => Carbon::now(),
                    'verification_code' => NULL,
                    'verification_code_generated_at' => NULL,
                ]);

                return JsonResponseService::successResponse('Email verified successfully. Please login to your account.');
            }

            return JsonResponseService::unauthorizedErrorResponse('The verification code is either invalid or expired. Please try again.');

        } catch (Throwable $th) {
            Log::info('**************************** Error in email verification ********************************');
            Log::info('Error is on line: '. $th->getLine());
            Log::error($th->getMessage());
            return JsonResponseService::errorResponse('Something went wrong. Please try again.');
        }
    }

    /**
     * Resend verification code endpoint.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resendVerificationCode(Request $request)
    {
        try {
            $rules = [
                'email' => ['required', 'exists:users,email']
            ];

            $data = $request->all();

            // Validate the incoming request data
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $message = 'Please fill the form correctly.';
                return JsonResponseService::validationErrorResponse($message, $validator->errors()->all());
            }

            $user = User::findByEmail($data['email']);

            if ($user->verification_code_generated_at) {
                // Get the resend code time from config
                $resendCodeTime = config('custom.resend_code_time');
                $verificationAlreadySent = $user->verification_code_generated_at->addMinutes($resendCodeTime);

                // Check if the verification code has already been sent within the specified time
                if (! $verificationAlreadySent->isPast()) {
                    return JsonResponseService::errorResponse('Verification code already sent to your email. Please try again after a minute.');
                }
            }

            $verificationCode = verificationCode(); //generate random verification code
            $user->update([
                'verification_code' => Hash::make($verificationCode),
                'verification_code_generated_at' => Carbon::now(),
            ]);

            // Send email containing verification code
            Mail::to($user->email)->send(new VerificationMail($user->name, $verificationCode, false));

            return JsonResponseService::successResponse('Please verify your email to continue.');

        } catch (Throwable $th) {
            Log::info('**************************** Error in resend email verification ********************************');
            Log::info('Error is on line: '. $th->getLine());
            Log::error($th->getMessage());
            return JsonResponseService::errorResponse('Something went wrong. Please try again.');
        }
    }
}
